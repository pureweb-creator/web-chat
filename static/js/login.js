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