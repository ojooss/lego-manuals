import 'bootstrap';
import './styles/app.css';

import Tooltip from 'bootstrap/js/dist/tooltip'

/**
 * Search-Bar
 */
let inputFilter = document.getElementById('input_filter');
if (inputFilter) {
    inputFilter.addEventListener('keyup', function () {
        let queryString = inputFilter.value;
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
        inputFilter.value = "";
        inputFilter.dispatchEvent(new Event('keyup'));
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
    researchButton.addEventListener('click', function (event) {
        if (researchInput.value.length > 3) {
            console.log('analysing ' + researchInput.value);
            fadeIn(document.getElementById('waiting-fog'));
            fadeIn(document.getElementById('waiting-container'));
            const xhr = new XMLHttpRequest();
            const url = "/import/autoload/" + researchInput.value;
            xhr.open("GET", url, true); // GET-Request konfigurieren
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText); // JSON-Daten parsen
                        console.log('response: ', response.documents);
                        document.getElementById("set_form_name").value = response.title;
                        let i = 0;
                        response.documents.forEach(
                            (pdfUrl) => {
                                let input = document.getElementById("set_form_manuals_" + i + "_url");
                                while (input === null && i < 100) {
                                    i++;
                                    input = document.getElementById("set_form_manuals_" + i + "_url");
                                }
                                input.value = pdfUrl;
                                addFileFieldToForm();
                                i++;
                            })
                        fadeOut(document.getElementById('waiting-fog'));
                        fadeOut(document.getElementById('waiting-container'));
                    } else {
                        console.error('error: ' + xhr.status + ' ' + xhr.statusText + ' ' + xhr.responseText);
                    }
                }
            };
            xhr.send();
        }
//        window.open('https://www.lego.com/de-de/service/buildinginstructions/' + researchInput.value);
    });
}


/**
 * add PDF to list
 */
document.body.addEventListener('click', function (e) {
    if (e.target.matches('#button-add-set')) {
        let collectionHolderClass = e.target.getAttribute('data-collection-holder-class');
        addFileFieldToForm(collectionHolderClass);
    }
});

// For the 'show' effect with "slow" animation, you need to implement custom logic
// as native JavaScript doesn't support jQuery's show/hide animations out of the box
function fadeIn(element) {
    element.style.display = 'block';
    element.style.opacity = '0';
    let opacity = 0;
    let interval = setInterval(function () {
        if (opacity < 1) {
            opacity += 0.05;
            element.style.opacity = opacity;
        } else {
            clearInterval(interval);
        }
    }, 30); // Adjust the timing here to control the speed of the animation
}

function fadeOut(element) {
    let opacity = 1;
    let interval = setInterval(function () {
        if (opacity > 0) {
            opacity -= 0.05;
            element.style.opacity = opacity;
        } else {
            clearInterval(interval);
        }
    }, 30); // Adjust the timing here to control the speed of the animation
    element.style.opacity = '0';
    element.style.display = 'none';
}


let formElement = document.getElementById('set_form');
if (formElement) {
    formElement.addEventListener('submit', function () {
        fadeOut(document.getElementById('waiting-fog'));
        fadeOut(document.getElementById('waiting-container'));
    });
}

document.addEventListener('keyup', function (event) {
    if (event.key === "Escape") { // Check if the Escape key was pressed
        fadeOut(document.getElementById('waiting-fog'));
        fadeOut(document.getElementById('waiting-container'));
    }
});


function addFileFieldToForm() {
    // Get the ul that holds the collection of tags
    let collectionHolder = document.querySelector('.file-collection');

    // Get the data-prototype explained earlier
    let newForm = collectionHolder.getAttribute('data-prototype');

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    newForm = newForm.replace(
        /__name__/g,
        document.querySelectorAll('.file-collection li').length + 1
    );

    // new rows may be empty
    newForm = newForm.replace(/required="required"/g,'');

    // Display the form in the page in an li, before the "Add a tag" link li
    let newFormLi = document.createElement('li');
    newFormLi.innerHTML = newForm;

    // Add the new form at the end of the list
    collectionHolder.appendChild(newFormLi);
}
