<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <title>ChatAja</title>
</head>

<body class="bg-dark text-white">
    @php
        $myId = Auth::user()->id;
    @endphp
    <div class="container mt-3">
        <div class="row justify-content-center">
            <div class="col-md-3 text-center">
                @forelse ($users as $user)
                    <p class="bg-secondary p-2 rounded-pill"><a id="{{ $user->id }}" href="#"
                            onclick="getUser({{ $user->id }})" class="nav-link">{{ $user->name }}</a>
                    </p>
                @empty
                    <p>No Users</p>
                @endforelse
            </div>
            <div class="col-md-6">
                <h5 class="mb-4" id="nameUser">Please select user</h5>
                <div class="input-group" id="form-send-message">

                </div>
                <div id="messages" class="container mt-3">
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    <script>
        let idUser = ""
        let messages = document.getElementById('messages')
        messages.innerHTML = ""
        let textEnd = ""

        function getUser(id) {
            messages.innerHTML = ""
            idUser = id
            let getNameUser = document.getElementById(idUser).innerText
            document.getElementById('nameUser').innerHTML = getNameUser

            let formMessage = document.getElementById('form-send-message')
            formMessage.innerHTML = `<input id="message" type="text" class="form-control" placeholder="Enter your message">
            <button onclick="sendMessage()" class="btn btn-primary">Send</button>
            <div id="message-invalid" class="invalid-feedback">
            </div>
            `
            // get chat from user ...
            getMessage()
        }

        function sendMessage() {
            let message = document.getElementById('message')
            message.classList.remove('is-invalid')
            let newMessage = message.value
            // show message
            messages.innerHTML += `<p class="text-primary text-end">${message.value}</p>`
            message.value = ""

            let csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            let formData = new FormData();
            formData.append('message', newMessage);
            formData.append('receiver_id', idUser);

            // send message to server
            fetch('/chat', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData,
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status == 422) {
                        message.classList.add('is-invalid')
                        document.getElementById('message-invalid').innerHTML = data.error
                    }
                })
                .catch(error => {
                    console.error('Ada kesalahan:', error.message);
                });
        }

        function getMessage() {
            messages.innerHTML = ""
            fetch('/chat/' + idUser, {
                    method: 'GET',
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    messages.innerHTML = ""
                    data.forEach(item => {
                        let myId = "{{ $myId }}"
                        if (item.sender_id == myId) {
                            textEnd = "text-end text-primary"
                        } else {
                            textEnd = "text-danger"
                        }
                        messages.innerHTML += `<p class="${textEnd}">${item.message}</p>`
                    });
                    textEnd = ""
                })
                .catch(error => {
                    console.error('Ada kesalahan:', error.message);
                });
        }


        let myId = "{{ $myId }}"
        // Enable pusher logging - don't include this in production
        Pusher.logToConsole = true;

        var pusher = new Pusher('02de2169c0ada92a8788', {
            cluster: 'ap1'
        });

        var channel = pusher.subscribe('my-channel');
        channel.bind('my-event', function(data) {
            if (myId == data.receiverId) {
                messages.innerHTML += `<p class="text-danger">${data.message}</p>`
            }
        });
    </script>

</body>

</html>
