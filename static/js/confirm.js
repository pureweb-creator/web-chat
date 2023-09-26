let confirm = new Vue({
    el: "#confirmApp",
    delimiters: ["[[", "]]"],
    data() {
        return {
            email: "",
            code: ['','','','',''],
            _token: "",
            response: {}
        }
    },

    mounted(){
        this._token = this.$refs._token.value
        this.email = this.$refs.email.value
    },

    methods: {
        pasteCode(e, i){
            let clipboardData = e.clipboardData || window.clipboardData;
            let pastedData = clipboardData.getData('Text');
            let inputCode = pastedData.trim().split('')

            this.code.forEach((element, index)=>{
                this.code[index] = inputCode[index]
            })

            // send form
            if (this.code.every(element => element !== ''))
                this.doConfirm()

            this.$refs.codeInput[this.code.length-1].focus()

        },
        manualEnterCode(e, i){
            // handle manual input. Allow only letters and numbers
            if (e.key >= '0' && e.key <= '9') {
                const allowedKeys = /^[a-zA-Z0-9\s]$/;
                if (i !== this.code.length - 1 && allowedKeys.test(e.key))
                    e.target.nextElementSibling.focus()

                // on backspace go back
                if (e.keyCode === 8 && i !== 0)
                    e.target.previousElementSibling.focus()

                // send form
                if (this.code.every(element => element !== ''))
                    this.doConfirm()
            }
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

                        if (this.response.success === false) {
                            setTimeout(() =>{
                                this.code = this.code.map(() => '')
                                this.$refs.codeInput[0].focus()
                            }, 100)
                        }

                        if (this.response.success === true)
                            window.location.href = "./"
                    }
                )
                .catch(error=>{console.log(error.message)})
        }
    },
});