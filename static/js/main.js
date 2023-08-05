$(document).ready(function(){

    if ($('#app').length){
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
                axios.get(`./home/loadFirstMessage?&message_to=${$('#messageTo').val()}&message_from=${$('#messageFrom').val()}`)
                    .then(response=>{
                        this.firstMessageID = response.data[0]?.id
                    });

                // получает сразу кол-во всех сообщений
                axios.get(`./home/loadMessages?offset=${this.loading_offset}&limit=${this.limit}&message_to=${$('#messageTo').val()}&message_from=${$('#messageFrom').val()}`)
                    .then(response=>{
                        this.messages = response.data.reverse()

                        // скроллим сразу все вниз при загрузке
                        this.scrollDownTrigger = !this.scrollDownTrigger

                        this.loading_offset+=100
                    });

                // opens websocket connection
                this.ws = new WebSocket(`ws://127.0.0.1:8000/ws/?user=${$('#messageFrom').val()}`);
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

                // not secure without CSRF :(
                deleteMessage(msgIdx, msgId){
                    let formData = new FormData()
                    formData.append('id', msgId)

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

                loadMessages: function (){

                    if (this.$refs.chatBody.scrollTop === 0) {

                        // проверяем, не доскролили ли мы до начала истории сообщений
                        if (this.messages[0].message_id !== this.firstMessageID) {

                            axios.get(`./home/loadMessages?offset=${this.loading_offset}&limit=${this.limit}&message_to=${$('#messageTo').val()}&message_from=${$('#messageFrom').val()}`)
                                .then(response => {

                                    let temp = []

                                    for (var i in response.data.reverse())
                                        temp.push(response.data[i])

                                    this.messages = temp.concat(this.messages)

                                    // скроллим к элементу, на котором остановились
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
                // при отравке сообщения, скроллить вниз до конца
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
    }

    if ($('#signupApp').length){
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

                                console.log(response.data)

                                if ( this.response.success === true ) {
                                    window.location.href = `./confirm?email=${this.email}`
                                }
                            }
                        )
                }
            },
        });
    }

    if ($('#loginApp').length){
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

                                if (this.response.success === true)
                                    window.location.href = `./confirm?email=${this.email}`
                            }
                        )
                }
            },
        });
    }

    if ($('#confirmApp').length){
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
                this.email = this.$refs.email.value
            },

            methods: {
                doConfirm: function () {
                    let formData = new FormData()
                    formData.append('code', this.code)
                    formData.append('_token', this._token)
                    formData.append('email', this.email)

                    axios({
                        method: "post",
                        url: "./confirm/process",
                        data: formData
                    })
                        .then(
                            response => {
                                this.response = response.data

                                if (this.response.success === true)
                                    window.location.href = "./"
                            }
                        )
                        .catch(error=>{console.log(error.message)})
                }
            },
        });
    }
});