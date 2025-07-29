// // Bus Image Gallery
// const busGallery = document.getElementById('busGallery');
// let isMouseDown = false;
// let startX;
// let scrollLeft;

// busGallery.addEventListener('mousedown', (e) => {
//     isMouseDown = true;
//     startX = e.pageX - busGallery.offsetLeft;
//     scrollLeft = busGallery.scrollLeft;
//     busGallery.style.cursor = 'grabbing'; 
// });

// busGallery.addEventListener('mouseleave', () => {
//     isMouseDown = false;
//     busGallery.style.cursor = 'grab';
// });

// busGallery.addEventListener('mouseup', () => {
//     isMouseDown = false;
//     busGallery.style.cursor = 'grab';
// });

// busGallery.addEventListener('mousemove', (e) => {
//     if (!isMouseDown) return;
//     e.preventDefault();
//     const x = e.pageX - busGallery.offsetLeft;
//     const walk = (x - startX) * 5; 
//     busGallery.scrollLeft = scrollLeft - walk;
// });

// // News Carousel
// let currentIndex = 0;

// function moveCarousel(direction) {
//     const items = document.querySelectorAll('.carousel-item');
//     const totalItems = items.length;

//     currentIndex = (currentIndex + direction + totalItems) % totalItems;

//     const carouselContainer = document.querySelector('.carousel-container');
//     carouselContainer.style.transform = `translateX(-${currentIndex * 33.33}%)`;  
// }
const busGallery = document.getElementById("busGallery");
let scrollAmount = 0;
const scrollSpeed = 0.7; // Adjust speed (smaller = slower)
const resetPoint = busGallery.scrollWidth / 2; // When to reset

function autoScroll() {
    scrollAmount += scrollSpeed;
    
    if (scrollAmount >= resetPoint) {
        scrollAmount = 0; // Reset position to create an infinite loop
    }
    
    busGallery.style.transform = `translateX(-${scrollAmount}px)`;
    requestAnimationFrame(autoScroll);
}

// Start auto-scrolling
autoScroll();




// // Present Date
// function setCurrentDate() {
//     const today = new Date();
//     const yyyy = today.getFullYear();
//     let mm = today.getMonth() + 1;
//     let dd = today.getDate();

//     if (mm < 10) mm = '0' + mm;
//     if (dd < 10) dd = '0' + dd; 

//     const formattedDate = `${yyyy}-${mm}-${dd}`;

//     document.getElementById('dateInput').value = formattedDate;
// }

// window.onload = setCurrentDate;

// Present Date
function setCurrentDate() {
    const today = new Date();
    const yyyy = today.getFullYear();
    let mm = today.getMonth() + 1;
    let dd = today.getDate();

    if (mm < 10) mm = '0' + mm;
    if (dd < 10) dd = '0' + dd;

    const formattedDate = `${yyyy}-${mm}-${dd}`;

    const dateInput = document.getElementById('dateInput');
    if (dateInput) {
        // Set today's date as the value
        dateInput.value = formattedDate;
        
        // Set the 'min' attribute to today's date
        dateInput.setAttribute('min', formattedDate);
    }
}

window.onload = setCurrentDate;
