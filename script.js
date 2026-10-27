// script.js
function calculate() {
    const input = document.getElementById('operationInput').value;
    if (input === '') return;

    // Parse the operation
    const operator = input.charAt(0);
    let value1 = parseFloat(input.slice(1));
    let value2 = parseFloat(document.getElementById('resultDisplay').innerText);

    switch (operator) {
        case '+':
            document.getElementById('resultDisplay').innerText = `${value1} + ${value2}`;
            break;
        case '-':
            document.getElementById('resultDisplay').innerText = `${value1} - ${value2}`;
            break;
        case '*':
            ctx.fillText(`${value1} * ${value2}`, 3, 50);
            break;
        case '/':
            if (value2 === 0) {
                console.warn('Division by zero is not allowed.');
                return;
            }
            ctx.fillText(`${value1} / ${value2}`, 3, 70);
            break;
        default:
            console.warn('Invalid operation.');
            return;
    }

    // Update the result display
    document.getElementById('resultDisplay').innerText = value1 + value2;

    // Clear input field after calculation
    document.getElementById('operationInput').value = '';
}
