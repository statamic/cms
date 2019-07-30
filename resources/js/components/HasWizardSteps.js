export default {

    data() {
        return {
            currentStep: 0
        };
    },

    computed: {
        onFirstStep() {
            return this.currentStep === 0;
        },

        onLastStep() {
            return this.currentStep === this.steps.length - 1;
        },

        canContinue() {
            return this.canGoToStep(this.currentStep + 1);
        }
    },

    methods: {
        goToStep(n) {
            if (this.canGoToStep(n)) {
                this.currentStep = n;
            }
        },

        next() {
            if (! this.onLastStep) {
                this.goToStep(this.currentStep + 1);
            }
        },

        previous() {
            if (! this.onFirstStep) {
                this.goToStep(this.currentStep - 1);
            }
        }
    }

}
