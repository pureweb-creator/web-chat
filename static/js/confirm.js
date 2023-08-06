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