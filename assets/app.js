import 'bootstrap';
import './styles/app.css';

import Tooltip from 'bootstrap/js/dist/tooltip'

/**
 * Search-Bar
 */
let inputFilter = document.getElementById('input_filter');
if (inputFilter) {
    inputFilter.addEventListener('keyup', function () {
        let queryString = document.getElementById('filter').value;
        let boxes = document.getElementsByClassName("manual-box");
        for (let i = 0; i < boxes.length; i++) {
            let box = boxes[i];
            let caption = box.getAttribute('data-filter');
            if (queryString.length < 3 || caption.search(new RegExp(queryString, "i")) > -1) {
                box.style.display = 'block';
            } else {
                box.style.display = 'none';
            }
        }
    });
    document.getElementById('filter_reset').addEventListener('click', function () {
        document.getElementById('input_filter').value = "";
        document.getElementById('input_filter').dispatchEvent(new Event('keyup'));
    });
}

/**
 * ToolTip
 */
let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new Tooltip(tooltipTriggerEl)
})


/**
 * lookup new set
 */
let researchInput = document.getElementById('research_input');
if (researchInput) {
    researchInput.addEventListener('change', function () {
        document.getElementById('set_form_number').value =
            document.getElementById('research_input').value;
    });
    let researchButton = document.getElementById('research_button');
    researchButton.addEventListener('click', function () {
        window.open('https://www.lego.com/de-de/service/buildinginstructions/' + researchInput.value);
    });
}
