import 'bootstrap';
import './styles/app.css';

import Tooltip from 'bootstrap/js/dist/tooltip'

/**
 * Search-Bar
 */
let inputFilter = document.getElementById('input_filter');
if (inputFilter) {
    inputFilter.addEventListener('keyup', function () {
        let queryString =inputFilter.value;
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
    researchButton.addEventListener('click', function () {
        window.open('https://www.lego.com/de-de/service/buildinginstructions/' + researchInput.value);
    });
}


/**
 * add PDF to list
 */
document.body.addEventListener('click', function(e) {
    if (e.target.matches('#button-add-set')) {
        let collectionHolderClass = e.target.getAttribute('data-collection-holder-class');
        addFormToCollection(collectionHolderClass);
    }
});

// For the 'show' effect with "slow" animation, you need to implement custom logic
// as native JavaScript doesn't support jQuery's show/hide animations out of the box
function fadeIn(element) {
    element.style.display = 'block';
    element.style.opacity = '0';
    let opacity = 0;
    let interval = setInterval(function() {
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
    let interval = setInterval(function() {
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
        document.getElementById('waiting-fog').style.display = 'block';
        let waitingContainer = document.getElementById('waiting-container');
        fadeIn(waitingContainer);
    });
}

document.addEventListener('keyup', function(event) {
    if (event.key === "Escape") { // Check if the Escape key was pressed
        fadeOut(
            document.getElementById('waiting-fog')
        );
        fadeOut(
            document.getElementById('waiting-container')
        );
    }
});


function addFormToCollection($collectionHolderClass) {
    // Get the ul that holds the collection of tags
    let collectionHolder = document.querySelector('.' + $collectionHolderClass);

    // Get the data-prototype explained earlier
    let newForm = collectionHolder.getAttribute('data-prototype');

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    newForm = newForm.replace(
        /__name__/g,
        document.querySelector('.file-collection li').length + 1
    );

    // Display the form in the page in an li, before the "Add a tag" link li
    let newFormLi = document.createElement('li');
    newFormLi.innerHTML = newForm;

    // Add the new form at the end of the list
    collectionHolder.appendChild(newFormLi);
}
