Issues and Improvements
HTML/JavaScript:
Handling Form Submission:

Ensure that form data is properly validated before sending a request to the backend.
Use const and let instead of var for variable declarations.
Password Handling:

The password handling is implemented with a regex and length check. Ensure that this aligns with your security requirements.
Error Handling:

Improve error messages and handling for better user experience.
Code Structure:

Refactor code into smaller functions for better readability and maintainability.
Security:

Avoid exposing sensitive data, such as your Stripe keys, in the frontend.
Here’s a refactored version of the JavaScript part:
 <script>
document.addEventListener('DOMContentLoaded', () => {
    const STRIPE_PUBLISHABLE_KEY = 'pk_test';
    const stripe = Stripe(STRIPE_PUBLISHABLE_KEY);
    const subscrFrm = document.querySelector('#subscrFrm');

    subscrFrm.addEventListener('submit', handleSubscrSubmit);

    const termsCheckbox = document.getElementById('aceptar_terminos');
    const contraInput = document.getElementById('contra');
    const contraSubmitButton = document.getElementById('boton2');
    const closeButton = document.getElementById('locierro');

    termsCheckbox.addEventListener('click', () => {
        document.getElementById('nocheck').style.display = 'none';
    });

    contraInput.addEventListener('click', () => {
        document.getElementById('psError2').style.display = 'none';
    });

    contraSubmitButton.addEventListener('click', () => {
        if (termsCheckbox.checked) {
            document.getElementById('cuadrogrande').style.filter = 'blur(10px)';
            document.getElementById('contracuadro').style.display = 'inherit';
        } else {
            document.getElementById('nocheck').style.display = 'inherit';
        }
    });

    closeButton.addEventListener('click', () => {
        document.getElementById('cuadrogrande').style.filter = 'inherit';
        document.getElementById('contracuadro').style.display = 'none';
    });

    const elements = stripe.elements();
    const cardElement = elements.create('card', {
        style: {
            base: {
                iconColor: '#333',
                color: '#333',
                fontWeight: '500',
                fontSize: '16px',
                ':-webkit-autofill': { color: '#fce883' },
                '::placeholder': { color: '#757575' }
            },
            invalid: {
                iconColor: '#e23c3c',
                color: '#e23c3c'
            }
        }
    });
    cardElement.mount('#card-element');
    cardElement.on('change', displayError);

    function displayError(event) {
        if (event.error) {
            showmonthsage(event.error.message);
        }
    }

    async function handleSubscrSubmit(e) {
        e.preventDefault();
        document.getElementById('cuadrogrande').style.filter = 'inherit';
        document.getElementById('contracuadro').style.display = 'none';

        const contra = document.getElementById('contra').value;
        const subscr_plan_id = document.getElementById('subscr_plan2').value;
        const tipocurrency = document.getElementById('tipocurrency2').value;
        const discount = document.getElementById('discount').value;

        if (validatePassword(contra)) {
            document.getElementById('paymentResponseContra').style.display = 'none';
            document.getElementById('paymentResponseContra').innerHTML = '';
            document.getElementById('frmProcess').style.display = 'inherit';

            try {
                const response = await fetch('/6QMH5f311WS1Go2bT71PRnUaLz26zlL/76Rc9Cc4Slk2mLmTO7/ximpn0LuEbL9iP1JL0/EN/dashboard?p=40', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        request_type: 'create_customer_subscription',
                        subscr_plan_id, tipocurrency, discount, contra
                    })
                });
                const data = await response.json();

                if (data.subscriptionId && data.clientSecret) {
                    paymentProcess(data.subscriptionId, data.clientSecret, data.customerId);
                } else {
                    handleError(data.error);
                }
            } catch (error) {
                showmonthsage('An error occurred. Please try again.');
            }
        } else {
            document.getElementById('paymentResponseContra').style.display = 'inherit';
            document.getElementById('paymentResponseContra').innerHTML = '*Incorrect password';
        }
    }

    function validatePassword(password) {
        const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@%!¡¿?#\.])[A-Za-z\d@%!¡¿?#\.]{8,40}$/;
        return pattern.test(password) && password.length <= 40;
    }

    async function paymentProcess(subscriptionId, clientSecret, customerId) {
        const contra = document.getElementById('contra').value;

        if (validatePassword(contra)) {
            const subscr_plan_id = document.getElementById('subscr_plan2').value;
            const tipocurrency = document.getElementById('tipocurrency2').value;
            const discount = document.getElementById('discount').value;
            const customer_name = 'prueba';

            try {
                const result = await stripe.confirmCardPayment(clientSecret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: { name: customer_name }
                    }
                });

                if (result.error) {
                    showmonthsage(result.error.message);
                } else {
                    const response = await fetch('/6QM...p=40', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            request_type: 'payment_insert',
                            subscription_id: subscriptionId,
                            customer_id: customerId,
                            subscr_plan_id,
                            payment_intent: result.paymentIntent,
                            tipocurrency,
                            discount,
                            contra
                        })
                    });
                    const data = await response.json();

                    if (data.payment_id) {
                        window.location.href = 'https://www.example.com/login?succes=1';
                    } else {
                        handleError(data.error);
                    }
                }
            } catch (error) {
                showmonthsage('An error occurred. Please try again.');
            }
        }
    }

    function handleError(error) {
        document.getElementById('frmProcess').style.display = 'none';
        document.getElementById('cuadrogrande').style.filter = 'inherit';
        document.getElementById('contracuadro').style.display = 'none';
        showmonthsage(error || 'An error occurred. Please try again.');
    }

    function showmonthsage(message) {
        const monthsageContainer = document.querySelector('#paymentResponse');
        document.getElementById('frmProcess').style.display = 'none';
        monthsageContainer.classList.remove('hidden');
        monthsageContainer.classList.add('hiddenMsg');
        monthsageContainer.textContent = '*' + message;
        setTimeout(() => {
            monthsageContainer.classList.add('hidden');
            monthsageContainer.textContent = '';
        }, 6000);
    }
});
 </script>
