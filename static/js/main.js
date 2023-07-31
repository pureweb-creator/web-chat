$(document).ready(function(){

    let app = new Vue({
        el: "#app",
        
        delimiters: ["[[", "]]"],
        data() {
            return {
                test: "",
                message_text: "",
                showEmojiPicker: false,
                loading_offset: 0,
                limit: 100,
                messagesCount: 0,
                firstMessageID: 0,
                scrollDownTrigger: false,
                isScrollUp: false,

                messages: [],
                message: {
                    message_text: "",
                    user_id: "",
                    message_pub_date: ""
                },
                ws: ""
            }
        },
        created: function () {
            axios.get(`./home/loadFirstMessage?&message_to=${$('textarea').attr('data-message-to')}&message_from=${$('textarea').attr('data-message-from')}`)
                .then(response=>{
                    this.firstMessageID = response.data[0]?.id
                });

            // получает сразу кол-во всех сообщений
            axios.get(`./home/loadMessages?offset=${this.loading_offset}&limit=${this.limit}&message_to=${$('textarea').attr('data-message-to')}&message_from=${$('textarea').attr('data-message-from')}`)
                .then(response=>{
                    for (var i in response.data)
                        this.messages.push(response.data[i])

                    // скроллим сразу все вниз при загрузке
                    this.scrollDownTrigger = !this.scrollDownTrigger
                    
                    this.loading_offset+=100
                });

            // opens websocket connection
            this.ws = new WebSocket(`ws://localhost:2346/?user=${$('textarea').attr('data-message-from')}`);
        },
        methods: {
            send: function (event){
                // if message is not empty
                if (this.message_text.trim().length === 0) return

                this.scrollDownTrigger = !this.scrollDownTrigger

                this.message.message_from = this.$refs.text_input.attributes[1].value
                this.message.message_to = this.$refs.text_input.attributes[2].value
                this.message.message_text = this.message_text
                this.message.message_pub_date = Date.now()

                this.ws.send(JSON.stringify(this.message))
                this.ws.onmessage = response => {

                    let parsed_response = JSON.parse(response.data);

                    this.messages.push(parsed_response[0])

                    this.loading_offset = 100

                    this.scrollDownTrigger = !this.scrollDownTrigger

                    this.message_text = ""
                    this.$refs.text_input.style.height = "45px"
                    this.$refs.chatBody.style.height = `calc(100% - 45px)`
                }
            },

            loadMessages: function (){

                if (this.$refs.chatBody.scrollTop === 0) {

                    // проверяем, не доскролили ли мы до начала истории сообщений
                    if (this.messages[0].message_id !== this.firstMessageID) {

                        axios.get(`./home/loadMessages?offset=${this.loading_offset}&limit=${this.limit}&message_to=${$('textarea').attr('data-message-to')}&message_from=${$('textarea').attr('data-message-from')}`)
                            .then(response => {

                                let temp = []

                                for (var i in response.data) {
                                    temp.push(response.data[i])
                                }
                                this.messages = temp.concat(this.messages)

                                localStorage.setItem('firstBeforeLoad', (temp.length-1).toString())

                                // change for watcher
                                this.isScrollUp = !this.isScrollUp
                            });
                    }

                    if (this.messages.length===this.loading_offset)
                        this.loading_offset+=100
                }
            },

            message_pub_time: function (date){
                date = date.split(' ')[1]
                return date.substr(date,date.length-3)
            },

            message_text_handling: function (msg){
                return msg.replaceAll('\n','<br>')
            },

            getEmoji: function(e){
                this.message_text += e.detail.unicode
            }
        },
        watch: {
            scrollDownTrigger() {
                this.$nextTick(()=>{
                    // при отравке сообщения, скроллить вниз до конца
                   this.$refs.chatBody.scrollTop = this.$refs.chatBody.scrollHeight
                });
            },

            isScrollUp(){
                let scrollElId = localStorage.getItem('firstBeforeLoad')
                location.href="#"+scrollElId
            },

            message_text: function(value){
                let numberOfLineBreaks = (value.match(/\n/g)||[]).length;

                // reset to defaults
                if (!numberOfLineBreaks){
                    this.$refs.text_input.style.height = "45px"
                    this.$refs.chatBody.style.height = `calc(100% - 45px)`
                }

                this.scrollDownTrigger = !this.scrollDownTrigger

                // adjust input height
                if (numberOfLineBreaks && numberOfLineBreaks<5) {
                    this.$refs.text_input.style.height = `${numberOfLineBreaks*20+40}px`
                    this.$refs.chatBody.style.height = `calc(100% - ${numberOfLineBreaks*20+40}px)`
                }
            }
        },
        computed: {
            messageText: function(){
                return this.message_text.trim().length > 0
            }
        }
    });

    let signUp = new Vue({
        el: "#signupApp",
        delimiters: ["[[","]]"],
        data(){
            return{
                name: "",
                email: "",
                _token: "",
                response: {}
            }
        },
        mounted(){
            this._token = this.$refs._token.value
        },
        methods: {
            doSignup: function (){
                let formData = new FormData()
                formData.append('name', this.name)
                formData.append('email', this.email)
                formData.append('_token', this._token)

                axios({
                    method: "post",
                    url: "./register/process",
                    data: formData
                })
                    .then(
                        response=>{
                            this.response = response.data

                            if ( this.response.success === true ) {
                                localStorage.setItem('email', this.email)
                                window.location.href = "./confirm"
                            }
                        }
                    )
            }
        },
    });

    let logIn = new Vue({
        el: "#loginApp",
        delimiters: ["[[", "]]"],
        data() {
            return {
                email: "",
                _token: "",
                response: {}
            }
        },

        mounted(){
            this._token = this.$refs._token.value
        },
        methods: {
            doLogin: function () {
                let formData = new FormData()
                formData.append('email', this.email)
                formData.append('_token', this._token)

                axios({
                    method: "post",
                    url: "login/process",
                    data: formData
                })
                    .then(
                        response => {
                            this.response = response.data

                            if (this.response.success === true) {
                                localStorage.setItem('email', this.email)
                                window.location.href = `./confirm`
                            }
                        }
                    )
            }
        },
    });

    let confirm = new Vue({
        el: "#confirmApp",
        delimiters: ["[[", "]]"],
        data() {
            return {
                email: "",
                code: "",
                _token: "",
                response: {}
            }
        },

        mounted(){
            this._token = this.$refs._token.value
        },

        methods: {
            doConfirm: function () {
                let formData = new FormData()
                formData.append('code', this.code)
                formData.append('_token', this._token)
                formData.append('email', localStorage.getItem('email'))

                axios({
                    method: "post",
                    url: "./confirm/process",
                    data: formData
                })
                    .then(
                        response => {
                            this.response = response.data

                            if (this.response.success === true) {
                                window.location.href = "./"
                            }
                        }
                    )
                    .catch(error=>{console.log(error.message)})
            }
        },
    });
});