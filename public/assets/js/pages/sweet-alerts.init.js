// Utility function to add event listener if element exists
function addEventListenerIfExists(id, event, callback) {
    const element = document.getElementById(id);
    if (element) {
        element.addEventListener(event, callback);
    }
}

addEventListenerIfExists("sa-basic", "click", function () {
    Swal.fire({ title: "Any fool can use a computer!" });
});

addEventListenerIfExists("sa-title", "click", function () {
    Swal.fire({
        title: "The Internet?",
        text: "That thing is still around?",
        icon: "question",
    });
});

addEventListenerIfExists("sa-success", "click", function () {
    Swal.fire({
        title: "Good job!",
        text: "You clicked the button!",
        icon: "success",
    });
});

addEventListenerIfExists("sa-long-content", "click", function () {
    Swal.fire({
        imageUrl: "https://placeholder.pics/svg/300x1500",
        imageHeight: 1500,
        imageAlt: "A tall image",
    });
});

addEventListenerIfExists("sa-custom-position", "click", function () {
    Swal.fire({
        position: "top-end",
        icon: "success",
        title: "Your work has been saved",
        showConfirmButton: false,
        timer: 1500,
    });
});

addEventListenerIfExists("sa-error", "click", function () {
    Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "Something went wrong!",
        footer: "<a href>Why do I have this issue?</a>",
    });
});

document.querySelectorAll(".sa-warning-btn").forEach((button) => {
    button.addEventListener("click", function (e) {
        e.preventDefault(); // Prevent default button behavior

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#28bb4b",
            cancelButtonColor: "#f34e4e",
            confirmButtonText: "Yes, delete it!",
        }).then((result) => {
            if (result.isConfirmed) {
                // Find the associated form and submit it
                const form = button.closest("form");
                if (form) {
                    form.submit();
                }
            }
        });
    });
});

addEventListenerIfExists("sa-params", "click", function () {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "No, cancel!",
        buttonsStyling: false,
    }).then(function (e) {
        if (e.value) {
            Swal.fire({
                title: "Deleted!",
                text: "Your file has been deleted.",
                icon: "success",
                confirmButtonColor: "#4a4fea",
            });
        } else if (e.dismiss === Swal.DismissReason.cancel) {
            Swal.fire({
                title: "Cancelled",
                text: "Your imaginary file is safe :)",
                icon: "error",
                confirmButtonColor: "#4a4fea",
            });
        }
    });
});

addEventListenerIfExists("sa-image", "click", function () {
    Swal.fire({
        title: "Adminto",
        text: "Responsive Bootstrap 5 Admin Dashboard",
        imageUrl: "assets/images/logo-sm.png",
        imageHeight: 50,
        confirmButtonColor: "#4a4fea",
        animation: false,
    });
});

addEventListenerIfExists("sa-close", "click", function () {
    let timerInterval;
    Swal.fire({
        title: "Auto close alert!",
        html: "I will close in <strong></strong> seconds.",
        timer: 2000,
        didOpen: () => {
            Swal.showLoading();
            timerInterval = setInterval(() => {
                Swal.getHtmlContainer().querySelector("strong").textContent =
                    Swal.getTimerLeft();
            }, 100);
        },
        willClose: () => {
            clearInterval(timerInterval);
        },
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.timer) {
            console.log("I was closed by the timer");
        }
    });
});

addEventListenerIfExists("custom-html-alert", "click", function () {
    Swal.fire({
        title: "<i>HTML</i> <u>example</u>",
        icon: "info",
        html: 'You can use <b>bold text</b>, <a href="//coderthemes.com/">links</a> and other HTML tags',
        showCloseButton: true,
        showCancelButton: true,
        confirmButtonText: '<i class="mdi mdi-thumb-up-outline"></i> Great!',
        cancelButtonText: '<i class="mdi mdi-thumb-down-outline"></i>',
    });
});

addEventListenerIfExists("custom-padding-width-alert", "click", function () {
    Swal.fire({
        title: "Custom width, padding, background.",
        width: 600,
        padding: 100,
        background:
            "#fff url(//subtlepatterns2015.subtlepatterns.netdna-cdn.com/patterns/geometry.png)",
    });
});

addEventListenerIfExists("ajax-alert", "click", function () {
    Swal.fire({
        title: "Submit email to run ajax request",
        input: "email",
        showCancelButton: true,
        confirmButtonText: "Submit",
        showLoaderOnConfirm: true,
        confirmButtonColor: "#4a4fea",
        cancelButtonColor: "#f34e4e",
        preConfirm: (email) => {
            return new Promise((resolve, reject) => {
                setTimeout(() => {
                    if (email === "taken@example.com") {
                        reject("This email is already taken.");
                    } else {
                        resolve();
                    }
                }, 2000);
            });
        },
        allowOutsideClick: false,
    }).then((result) => {
        Swal.fire({
            icon: "success",
            title: "Ajax request finished!",
            confirmButtonColor: "#4a4fea",
            html: "Submitted email: " + result.value,
        });
    });
});

addEventListenerIfExists("chaining-alert", "click", function () {
    Swal.mixin({
        input: "text",
        confirmButtonText: "Next &rarr;",
        showCancelButton: true,
        confirmButtonColor: "#4a4fea",
        cancelButtonColor: "#74788d",
        progressSteps: ["1", "2", "3"],
    })
        .queue([
            {
                title: "Question 1",
                text: "Chaining swal2 modals is easy",
            },
            "Question 2",
            "Question 3",
        ])
        .then((result) => {
            if (result.value) {
                Swal.fire({
                    title: "All done!",
                    confirmButtonColor: "#4a4fea",
                    html:
                        "Your answers: <pre><code>" +
                        JSON.stringify(result.value) +
                        "</code></pre>",
                    confirmButtonText: "Lovely!",
                });
            }
        });
});

addEventListenerIfExists("dynamic-alert", "click", function () {
    swal.queue([
        {
            title: "Your public IP",
            confirmButtonColor: "#4a4fea",
            confirmButtonText: "Show my public IP",
            text: "Your public IP will be received via AJAX request",
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return new Promise((resolve) => {
                    $.get("https://api.ipify.org?format=json").done((data) => {
                        swal.insertQueueStep(data.ip);
                        resolve();
                    });
                });
            },
        },
    ]).catch(swal.noop);
});
