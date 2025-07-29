<!-- <section class="available-buses">
            <h2>Available Travel Bus</h2>
            <div class="bus-gallery" id="busGallery">
                <img src="images/bus1.jpg" alt="Bus 1">
                <img src="images/bus2.jpg" alt="Bus 2">
                <img src="images/bus3.jpg" alt="Bus 3">
                <img src="images/bus4.jpg" alt="Bus 4">
                <img src="images/bus5.avif" alt="Bus 5">
                <img src="images/bus6.jpg" alt="Bus 6">
                <img src="images/bus1.jpg" alt="Bus 1">
                <img src="images/bus2.jpg" alt="Bus 2">
                <img src="images/bus3.jpg" alt="Bus 3">
                <img src="images/bus4.jpg" alt="Bus 4">
                <img src="images/bus5.avif" alt="Bus 5">
                <img src="images/bus6.jpg" alt="Bus 6">
                <img src="images/bus1.jpg" alt="Bus 1">
                <img src="images/bus2.jpg" alt="Bus 2">
                <img src="images/bus3.jpg" alt="Bus 3">
                <img src="images/bus4.jpg" alt="Bus 4">
                <img src="images/bus5.avif" alt="Bus 5">
                <img src="images/bus6.jpg" alt="Bus 6">
            </div>
        </section> -->
<style>
    .bus-gallery {
    overflow: hidden;
    width: 100%;
    background-color: rgb(202, 201, 201);
    padding: 10px;
    display: flex;
    justify-content: center;
}

/* Wrapper holding images */
.gallery-wrapper {
    display: flex;
    gap: 2rem;
    width: max-content;
    animation: scrollGallery 20s linear infinite;
}

/* Ensure images are displayed correctly */
.gallery-wrapper img {
    max-width: 200px; /* Adjust width if needed */
    height: auto;
    border-radius: 8px;
}

/* Keyframes for smooth infinite scrolling */
@keyframes scrollGallery {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-100%);
    }
}

</style>

<div class="bus-gallery">
    <div class="gallery-wrapper">
        <img src="images/bus1.jpg" alt="Bus 1">
        <img src="images/bus2.jpg" alt="Bus 2">
        <img src="images/bus3.jpg" alt="Bus 3">
        <img src="images/bus4.jpg" alt="Bus 4">
        <img src="images/bus5.avif" alt="Bus 5">
        <img src="images/bus6.jpg" alt="Bus 6">
        <img src="images/bus1.jpg" alt="Bus 1">
        <img src="images/bus2.jpg" alt="Bus 2">
        <img src="images/bus3.jpg" alt="Bus 3">
        <img src="images/bus4.jpg" alt="Bus 4">
        <img src="images/bus5.avif" alt="Bus 5">
        <img src="images/bus6.jpg" alt="Bus 6">
    </div>
</div>


<script>
   document.addEventListener("DOMContentLoaded", function () {
    const gallery = document.querySelector(".gallery-wrapper");
    const images = Array.from(gallery.children);
    
    // Clone all images and append them immediately
    images.forEach((img) => {
        let clone = img.cloneNode(true);
        gallery.appendChild(clone);
    });

    // Ensure no delay by setting the animation state
    gallery.style.animationPlayState = 'running';
});


</script>