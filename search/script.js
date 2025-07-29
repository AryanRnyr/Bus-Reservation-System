

// old suggestion without specific
// function showSuggestions(type) {
//     document.getElementById("suggestions-" + type).style.display = "block";
// }

// function fetchSuggestions(type) {
//     const input = document.getElementById(type).value;
    
//     if (input.length === 0) {
//         document.getElementById("suggestions-" + type).style.display = "none";
//         return;
//     }

//     // Create an AJAX request
//     const xhr = new XMLHttpRequest();
//     xhr.open("POST", "fetch_suggestions.php", true);
//     xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

//     xhr.onreadystatechange = function() {
//         if (xhr.readyState === 4 && xhr.status === 200) {
//             const suggestionsDiv = document.getElementById("suggestions-" + type);
//             suggestionsDiv.innerHTML = xhr.responseText;
//             suggestionsDiv.style.display = "block";
//         }
//     };
    
//     // Send the user's input and the type (departure or arrival) to the server-side script
//     xhr.send("query=" + encodeURIComponent(input) + "&type=" + encodeURIComponent(type));
// }

// // Hide suggestions if the user clicks outside the input or suggestions list
// document.addEventListener("click", function(event) {
//     if (!event.target.closest("#departure") && !event.target.closest("#suggestions-departure")) {
//         document.getElementById("suggestions-departure").style.display = "none";
//     }
//     if (!event.target.closest("#arrival") && !event.target.closest("#suggestions-arrival")) {
//         document.getElementById("suggestions-arrival").style.display = "none";
//     }
// });

// // Set the input value to the clicked suggestion and hide the suggestions list
// function selectSuggestion(type, value) {
//     document.getElementById(type).value = value;
//     document.getElementById("suggestions-" + type).style.display = "none";
// }

let currentSuggestions = {
    departure: [],
    arrival: []
};

function showSuggestions(type) {
    document.getElementById("suggestions-" + type).style.display = "block";
}

function fetchSuggestions(type) {
    const input = document.getElementById(type).value;

    if (input.length === 0) {
        document.getElementById("suggestions-" + type).style.display = "none";
        currentSuggestions[type] = []; // Clear suggestions when input is empty
        return;
    }

    // Create an AJAX request
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "fetch_suggestions.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const suggestionsDiv = document.getElementById("suggestions-" + type);
            suggestionsDiv.innerHTML = xhr.responseText;
            suggestionsDiv.style.display = "block";

            // Update currentSuggestions with the fetched options
            currentSuggestions[type] = Array.from(suggestionsDiv.querySelectorAll("div")).map(div => div.textContent.trim());
        }
    };

    // Send the user's input and the type (departure or arrival) to the server-side script
    xhr.send("query=" + encodeURIComponent(input) + "&type=" + encodeURIComponent(type));
}

// Helper function to validate input against suggestions and clear if not valid
function validateAndClear(type) {
    const input = document.getElementById(type);
    const suggestions = currentSuggestions[type];

    // If the input value is not in suggestions, clear it
    if (!suggestions.includes(input.value.trim())) {
        input.value = '';
    }
}



// Add blur event listeners with a delay to allow selecting suggestions
document.getElementById("departure").addEventListener("blur", function() {
    setTimeout(() => {
        validateAndClear("departure");
        document.getElementById("suggestions-departure").style.display = "none";
    }, 150); // 150ms delay to allow click event on suggestion to register
});

document.getElementById("arrival").addEventListener("blur", function() {
    setTimeout(() => {
        validateAndClear("arrival");
        document.getElementById("suggestions-arrival").style.display = "none";
    }, 150); // 150ms delay to allow click event on suggestion to register
});

// Set the input value to the clicked suggestion and hide the suggestions list
function selectSuggestion(type, value) {
    document.getElementById(type).value = value;
    document.getElementById("suggestions-" + type).style.display = "none";
}
