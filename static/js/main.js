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
                    user_name: "",
                    message_text: "",
                    user_id: "",
                    message_pub_date: ""
                },
                ws: ""
            }
        },
        created: function () {
            // получает сразу кол-во всех сообщений
            axios.get(`action/load/messages/first`)
                .then(response=>{
                    this.firstMessageID = response.data.id
                });

            axios.get(`action/load/messages?offset=${this.loading_offset}&limit=${this.limit}`)
                .then(response=>{
                    for (var i in response.data)
                        this.messages.push(response.data[i])

                    // скроллим сразу все вниз при загрузке
                    this.scrollDownTrigger = !this.scrollDownTrigger
                    
                    this.loading_offset+=100
                });

            // opens websocket connection
            this.ws = new WebSocket('ws://localhost:2346');
        },
        methods: {
            newLine: function (){
                this.scrollDownTrigger = !this.scrollDownTrigger
                this.$refs.text_input.style.height = this.$refs.text_input.scrollHeight+"px"
            },


            send: function (event){

                // if message is not empty
                if (this.message_text.trim().length == 0) return

                this.scrollDownTrigger = !this.scrollDownTrigger

                this.message.user_name = this.$refs.text_input.attributes[1].value
                this.message.user_id = this.$refs.text_input.attributes[2].value
                this.message.message_text = this.message_text
                this.message.message_pub_date = Date.now()

                this.ws.send(JSON.stringify(this.message))
                this.ws.onmessage = response => {

                    this.messages = [];
                    let parsed_response = JSON.parse(response.data);

                    for (var i in parsed_response)
                        this.messages.push(parsed_response[i])

                    this.loading_offset = 100

                    this.scrollDownTrigger = !this.scrollDownTrigger
                }

                this.message_text = ""
                this.$refs.text_input.style.height = "auto"

            },

            loadMessages: function (){

                if (this.$refs.chatBody.scrollTop === 0) {
                    // проверяем, не доскролили ли мы до начала истории сообщений

                    if (this.messages[0].id !== this.firstMessageID) {
                        axios.get(`action/load/messages?offset=${this.loading_offset}&limit=${this.limit}`)
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
                console.log(scrollElId)
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
                response: {}
            }
        },
        methods: {
            doSignup: function (){
                let formData = new FormData()
                formData.append('name', this.name)
                formData.append('email', this.email)

                axios({
                    method: "post",
                    url: "action/signup",
                    data: formData
                })
                    .then(
                        response=>{
                            this.response = response.data
                            console.log(this.response)

                            if ( this.response.ok === true ) {
                                window.location.href = "action/confirm?email="+this.email
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
                response: {}
            }
        },

        methods: {
            doLogin: function () {
                let formData = new FormData()
                formData.append('email', this.email)

                axios({
                    method: "post",
                    url: "action/login",
                    data: formData
                })
                    .then(
                        response => {
                            this.response = response.data
                            console.log(this.response)

                            if (this.response.ok === true) {
                                window.location.href = "action/confirm?email="+this.email
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
                response: {}
            }
        },

        methods: {
            doConfirm: function () {

                this.email = this.$refs.email.value;

                let formData = new FormData()
                formData.append('email', this.email)
                formData.append('code', this.code)

                axios({
                    method: "post",
                    url: "auth",
                    data: formData
                })
                    .then(
                        response => {
                            this.response = response.data
                            console.log(this.response)

                            if (this.response.ok === true) {
                                window.location.href = "/chat"
                            }
                        }
                    )
                    .catch(error=>{console.log(error.message)})
            }
        },
    });

});