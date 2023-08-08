let confirm = new Vue({
    el: "#confirmApp",
    delimiters: ["[[", "]]"],
    data() {
        return {
            email: "",
            code: [null,null,null,null,null],
            _token: "",
            response: {}
        }
    },

    mounted(){
        this._token = this.$refs._token.value
        this.email = this.$refs.email.value
    },

    methods: {
        fillCode(event, i){
            // handle Ctrl+V hotkey and fill all inputs
            if (event.keyCode===86 && event.ctrlKey) {
                let inputCode = event.target.value.trim().split('')
                this.code.splice(0, inputCode.length, ...inputCode)

                let currentSibling = event.target
                for (let i=0; i<this.code.length; i++){
                    if (this.code[i]!==null)
                        currentSibling.classList.add("active")
                    currentSibling = currentSibling.nextElementSibling
                }

                this.doConfirm()
            }

            // handle manual input. Allow only letters and numbers
            const allowedKeys = /^[a-zA-Z0-9\s]$/;
            if (i!==this.code.length-1 && allowedKeys.test(event.key))
                event.target.nextElementSibling.focus()

            // on backspace go back
            if (event.keyCode===8 && i!==0)
                event.target.previousElementSibling.focus()

            // add active class to filled input
            this.code[i] ? event.target.classList.add("active") : event.target.classList.remove("active")

            // send form
            if (this.code.every(element => element !== null))
                this.doConfirm()
        },

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