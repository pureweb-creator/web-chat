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
                            window.location.href = `./confirm?email=${this.email}`
                        }
                    }
                )
        }
    },
});