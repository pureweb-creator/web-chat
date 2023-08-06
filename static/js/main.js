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

            let formData = new FormData()
            formData.append('message_to', $('#messageTo').val() ?? '')
            formData.append('message_from', $('#messageFrom').val() ?? '')
            formData.append('_token', this._token)

            // Get first message ID
            axios({
                method: "POST",
                url: "./home/loadFirstMessage",
                data: formData
            })
                .then(response=>{
                    this.firstMessageID = response.data[0]?.id
                })

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

                    if (response.data.length)
                        this.messages = response.data.reverse()

                    // скроллим сразу все вниз при загрузке
                    this.scrollDownTrigger = !this.scrollDownTrigger

                    this.loading_offset+=100
                })

            // opens websocket connection
            this.ws = new WebSocket(`ws://127.0.0.1:8000/ws/?user=${$('#messageFrom').val() ?? ''}`);
        },
        methods: {
            onInput(e) {
                this.message.message_text = e.target.innerText

                // if new line, scroll entire dialog body
                this.scrollDownTrigger = !this.scrollDownTrigger
            },

            send: function (event){

                if (this.message.message_text.trim().length === 0)
                    return

                // скроллим до конца вниз
                this.scrollDownTrigger = !this.scrollDownTrigger

                this.message.message_from = $('#messageFrom').val()
                this.message.message_to = $('#messageTo').val()

                this.ws.send(JSON.stringify(this.message))
                this.ws.onmessage = response => {
                    let parsed_response = JSON.parse(response.data);

                    this.messages.push(parsed_response[0])

                    this.loading_offset = 100

                    this.scrollDownTrigger = !this.scrollDownTrigger

                    this.message.message_text = ""
                    this.$refs.textInput.innerText = ""
                }
            },

            copyToCliptray(msgText){
                let strippedText = msgText.replace(/(<([^>]+)>)/gi, "")
                navigator.clipboard.writeText(strippedText)
            },

            deleteMessage(msgIdx, msgId){
                let formData = new FormData()
                formData.append('id', msgId)
                formData.append('_token', this._token)
                formData.append('message_from', $('#messageFrom').val() ?? '')

                axios({
                    method: "post",
                    url: "./home/deleteMessage",
                    data: formData
                })
                    .then(
                        response=>{
                            this.response = response.data

                            // if deleted in db, then delete in array in frontend
                            if (this.response.success === true){
                                if (msgIdx > -1)
                                    this.messages.splice(msgIdx, 1)
                            }
                        }
                    )
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