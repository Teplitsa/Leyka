function printCardCheck() {

    const printContent = document.getElementById('card-check-text').innerHTML;
    const originalContent = document.body.innerHTML;

    document.body.innerHTML = printContent;

    window.print();

    document.body.innerHTML = originalContent;

    bindNoticeCloseEvent();

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

                let $tools = document.getElementById('card-check-tools');

                $tools.classList.add('leyka-pf--notice-open');

            }

        }

    });

    request.send(params);

}

function bindNoticeCloseEvent() {

    let $noticeClose = document.querySelector('#card-check-tools .notice__close');

    $noticeClose.addEventListener('click', function (e) {

        e.preventDefault();

        document.getElementById('card-check-tools').classList.remove('leyka-pf--notice-open');

    })

}

bindNoticeCloseEvent();

