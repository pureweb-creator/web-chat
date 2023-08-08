$(document).ready(function(){
    let app = new Vue({
        el: "#app",
        delimiters: ["[[", "]]"],
        data() {
            return {
                showEmojiPicker: false,
                loading_offset: 0,
                limit: 100,
                messagesCount: 0,
                firstMessageID: 0,
                scrollDownTrigger: false,
                activeItem: null,
                _token: "",
                messages: [],
                message: {
                    message_text: "",
                    user_id: "",
                    message_from: "",
                    message_to: ""
                },
                test: "",
                ws: ""
            }
        },
        created: function () {
            this._token = document.querySelector('meta[name="_token"]').content
            axios.defaults.headers.common['X-CSRF-TOKEN'] = this._token;

            this.loadFirstPack()

            // opens websocket connection
            this.ws = new WebSocket(`ws://127.0.0.1:8000/ws?user=${$('#messageFrom').val() ?? ''}`);
            // Listen websocket for a response
            this.wsListen()
        },
        methods: {
            onInput(e) {
                this.message.message_text = e.target.innerText

                // if new line, scroll entire dialog body
                this.scrollDownTrigger = !this.scrollDownTrigger
            },

            addMessage: function (){
                if (this.message.message_text.trim().length === 0)
                    return

                this.scrollDownTrigger = !this.scrollDownTrigger
                this.message.message_from = $('#messageFrom').val()
                this.message.message_to = $('#messageTo').val()

                this.ws.send(JSON.stringify({
                    'action': 'addMessage',
                    'message': JSON.stringify(this.message)
                }))

                // hide emoji picker if was selected
                if (this.showEmojiPicker)
                    this.showEmojiPicker = false
            },

            deleteMessage(msgId){
                this.ws.send(JSON.stringify({
                    'action': 'deleteMessage',
                    'message': JSON.stringify({
                        'message_id': msgId,
                        'message_from': $('#messageFrom').val() ?? '',
                        'message_to': $('#messageTo').val() ?? ''
                    })
                }))
            },

            // Load new pack of messages on scroll up
            loadMessages: function (){
                // check if we are scrolling up
                if (this.$refs.chatBody.scrollTop === 0) {

                    // check if we scrolled to the beginning of the message history
                    if (this.messages[0].message_id !== this.firstMessageID) {

                        let formData = new FormData()
                        formData.append('offset', this.loading_offset)
                        formData.append('limit', this.limit)
                        formData.append('message_to', $('#messageTo').val() ?? '')
                        formData.append('message_from', $('#messageFrom').val() ?? '')
                        formData.append('_token', this._token)

                        axios({
                            method: "POST",
                            url: "./home/loadMessages",
                            data: formData
                        })
                            .then(response => {

                                let temp = []

                                for (var i in response.data.reverse())
                                    temp.push(response.data[i])

                                this.messages = temp.concat(this.messages)

                                // scroll to the element where we left off
                                location.href="#"+(temp.length)
                            });
                    }

                    if (this.messages.length===this.loading_offset)
                        this.loading_offset+=100
                }
            },

            getTimeFromDateTime: function (date){
                date = date.split(' ')[1]
                return date.substr(date,date.length-3)
            },

            getEmoji: function(e){
                this.$refs.textInput.innerText += e.detail.unicode
                this.message.message_text += e.detail.unicode
            },

            copyToCliptray(msgText){
                
                let strippedText = msgText.replace(/(<([^>]+)>)/gi, "")
                var TempText = document.createElement("input");
                TempText.value = strippedText;
                document.body.appendChild(TempText);
                TempText.select();
                
                document.execCommand("copy");
                document.body.removeChild(TempText);
                
                // works only over https
                // navigator.clipboard.writeText(strippedText)
            },

            // listen for a websocket response
            wsListen: function (){
                this.ws.onmessage = response => {
                    let parsed_response = JSON.parse(response.data)

                    switch (parsed_response.action){
                        case "addMessage":
                            this.messages.push(parsed_response.data[0])

                            this.loading_offset = 100
                            this.scrollDownTrigger = !this.scrollDownTrigger

                            // reset to defaults
                            this.message.message_text = ""
                            this.$refs.textInput.innerText = ""

                            break

                        case "deleteMessage":
                            // replace existing messages array with given (with deleted items)
                            if (parsed_response.success===true)
                                this.messages = parsed_response.data.reverse()
                            break
                    }
                }
            },

            loadFirstPack: function (){
                // Get first pack of messages
                formData = new FormData()
                formData.append('offset', this.loading_offset)
                formData.append('limit', this.limit)
                formData.append('message_to', $('#messageTo').val() ?? '')
                formData.append('message_from', $('#messageFrom').val() ?? '')
                formData.append('_token', this._token)

                axios({
                    method: "POST",
                    url: "./home/loadMessages",
                    data: formData
                })
                    .then(response=>{
                        if (response.data.length) {
                            this.firstMessageID = response.data[response.data.length-1]?.message_id
                            this.messages = response.data.reverse()
                        }

                        this.scrollDownTrigger = !this.scrollDownTrigger
                        this.loading_offset+=100
                    })
            }
        },
        watch: {
            // scroll to the very bottom
            scrollDownTrigger() {
                this.$nextTick(()=>{
                    this.$refs.chatBody.scrollTop = this.$refs.chatBody.scrollHeight
                });
            }
        },
        computed: {
            messageNotEmpty: function(){
                return this.message.message_text.trim().length > 0
            }
        }
    });
});