document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("rentalModal");
    const closeBtn = document.querySelector(".close");
    const rentButtons = document.querySelectorAll(".rent-now-btn");

    rentButtons.forEach(button => {
        button.addEventListener("click", function () {
            document.getElementById("modalProductId").value = this.dataset.productId;
            document.getElementById("productName").value = this.dataset.productName;
            modal.style.display = "block";
        });
    });

    closeBtn.addEventListener("click", function () {
        modal.style.display = "none";
    });

    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    const rentalDuration = document.getElementById("rentalDuration");
    const rentalStart = document.getElementById("rentalStart");
    const rentalEnd = document.getElementById("rentalEnd");

    function updateEndDate() {
        if (rentalStart.value && rentalDuration.value) {
            let startDate = new Date(rentalStart.value);
            startDate.setDate(startDate.getDate() + parseInt(rentalDuration.value));
            rentalEnd.value = startDate.toISOString().split('T')[0];
        }
    }

    rentalStart.addEventListener("change", updateEndDate);
    rentalDuration.addEventListener("input", updateEndDate);
});
