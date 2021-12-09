function printCardCheck() {

    const printContent = document.getElementById('card-check-text').innerHTML;
    const originalContent = document.body.innerHTML;

    document.body.innerHTML = printContent;

    window.print();

    document.body.innerHTML = originalContent;

}

function sendCardCheck(donationId) {

    const ajaxUrl = '/wp-admin/admin-ajax.php';
    const request = new XMLHttpRequest();
    const params = "action=send-card-check&donation_id="+donationId;

    request.open("POST", ajaxUrl, true);
    request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    request.addEventListener("readystatechange", () => {

        if(request.readyState === 4 && request.status === 200) {
            if (request.responseText === '1') {
                alert('Card-check has been sent to your email!');
            };

        }

    });

    request.send(params);

}

