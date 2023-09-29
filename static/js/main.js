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
                isActiveUsersPanel: false,
                _token: "",
                isStartTyping: null,
                typingEvent: null,
                isVisibleScrollDownArrow: false,
                messages: [],
                message: {
                    message_text: "",
                    user_id: "",
                    message_from: "",
                    message_to: ""
                },
                user: {
                    isOnline: null,
                    isTyping: null,
                    lastSeen: null
                },
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
            inputText(e) {
                this.message.message_text = e.target.innerText

                // if new line, scroll entire dialog body
                this.scrollDownTrigger = !this.scrollDownTrigger
            },
            pasteText(e) {                
                // Get the copied text from the clipboard
                const text = e.clipboardData
                    ? (e.originalEvent || e).clipboardData.getData('text/plain')
                    : // For IE
                    window.clipboardData
                    ? window.clipboardData.getData('Text')
                    : '';

                this.message.message_text += text
        
                // Insert text at the current position of caret
                const range = document.getSelection().getRangeAt(0);
                range.deleteContents();
    
                const textNode = document.createTextNode(text);
                range.insertNode(textNode);
                range.selectNodeContents(textNode);
                range.collapse(false);
    
                const selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(range);
            },
            trackTypingEvent(e){
                if (this.isStartTyping==null)
                    this.isStartTyping=true

                if (this.isStartTyping){
                    this.ws.send(JSON.stringify({
                        'action': 'startTyping',
                        'message_to': $('#messageTo').val()
                    }))

                    this.isStartTyping=false
                }

                if(this.typingEvent) {
                    clearTimeout(this.typingEvent);
                    this.typingEvent = null
                }

                this.typingEvent = setTimeout(() =>  {
                    this.ws.send(JSON.stringify({
                        'action': 'endTyping',
                        'message_to': $('#messageTo').val()
                    }))

                    this.isStartTyping=null
                }, 1500)

            },
            openContextMenu: function(index){
                this.activeItem = this.activeItem == index ? null : index;
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

                this.ws.send(JSON.stringify({
                    'action': 'endTyping',
                    'message_to': $('#messageTo').val()
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

                this.activeItem=null
            },
            // Load new pack of messages on scroll up
            loadMessages: function (){

                // check if we are scrolling up
                if (this.$refs.chatBody.scrollTop === 0) {

                    // check if we scrolled to the beginning of the messages history
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
                // Decode html entities
                var textArea = document.createElement('textarea');
                textArea.innerHTML = msgText;

                let strippedText = textArea.value.replace(/(<([^>]+)>)/gi, "")
                var TempText = document.createElement("input");
                TempText.value = strippedText;
                document.body.appendChild(TempText);
                TempText.select();

                document.execCommand("copy");
                document.body.removeChild(TempText);

                this.activeItem = null

                // works only over https
                // navigator.clipboard.writeText(strippedText)
            },
            // listen for a websocket response
            wsListen: function (){
                this.ws.onmessage = response => {
                    let parsed_response = JSON.parse(response.data)

                    switch (parsed_response.action){
                        case "onConnect":
                        case "onDisconnect":
                            parsed_response.data.forEach(element => {

                                if (parseInt(element.id) !== parseInt($('#messageTo').val()))
                                    return false

                                let now = new Date()
                                let lastSeen = new Date(element.last_seen)
                                let lastSeenInMilliseconds = new Date(element.last_seen).getTime()
                                let lastSeenInMinutes = Math.floor((now-lastSeen)/1000/60)
                                let lastSeenInHours = Math.floor(lastSeenInMinutes/60)
                                let lastSeenInDays = Math.floor(lastSeenInHours/24)

                                let lastSeenHourMinute = (new Date(lastSeenInMilliseconds).toLocaleTimeString()).slice(0, 5)
                                let lastSeenMonthDay = (new Date(lastSeenInMilliseconds).toLocaleDateString()).slice(0, 5)
                                let lastSeenYear = (new Date(lastSeenInMilliseconds).toLocaleDateString()).slice(6)

                                let lastSeenString

                                if (lastSeenInMinutes<1)
                                    lastSeenString = `last seen just now`

                                if (lastSeenInDays < 1)
                                    lastSeenString = `last seen at ${lastSeenHourMinute}`

                                if (lastSeenInDays===1)
                                    lastSeenString = `last seen yesterday at ${lastSeenHourMinute}`

                                if (lastSeenInDays > 1)
                                    lastSeenString = `last seen ${lastSeenMonthDay} at ${lastSeenHourMinute}`

                                if (lastSeenInDays >= 365)
                                    lastSeenString = `last seen ${lastSeenMonthDay}.${lastSeenYear} at ${lastSeenHourMinute}`

                                this.user.isOnline = element.online
                                this.user.lastSeen = this.user.isOnline ? "online" : lastSeenString

                            });
                            break
                        case "onStartTyping":
                            this.user.isTyping=true
                            break
                        case "onEndTyping":
                            this.user.isTyping=false
                            break
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
            },
            scrollDownHandler: function(){
                let scrollHeigth = this.$refs.chatBody.scrollHeight
                let scrollTop = this.$refs.chatBody.scrollTop
                let clientHeigth = this.$refs.chatBody.clientHeight

                this.isVisibleScrollDownArrow = (scrollHeigth - (scrollTop + clientHeigth)) >= 300
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
        },
        filters: {
            truncate: function (value){
                return value.slice(0, 1)
            }
        }
    });
});