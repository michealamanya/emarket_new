<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to bottom right, #6a11cb, #2575fc); /* Gradient background */
            color: #fff; /* Change text color for better visibility */
        }
        .container {
            background-color: rgba(255, 255, 255, 0.9); /* White background with transparency for the container */
            border-radius: 10px; /* Rounded corners */
            padding: 20px; /* Add some padding */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* Add shadow for depth */
        }
        #localVideo, #remoteVideo {
            width: 100%;
            max-width: 400px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
        }
        #chatBox {
            border: 1px solid #ccc;
            padding: 10px;
            height: 300px;
            overflow-y: auto;
            margin-bottom: 10px;
            background-color: #f8f9fa;
        }
        .message {
            margin: 5px 0;
            padding: 5px;
            border-radius: 5px;
        }
        .my-message {
            background-color: #d1e7dd;
            text-align: right;
        }
        .admin-message {
            background-color: #f8d7da;
        }
        .emoji-picker {
            position: absolute;
            background-color: white;
            border: 1px solid #ccc;
            padding: 10px;
            display: none;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Support</h2>
        <div class="mb-3">
            <button id="startAudioCall" class="btn btn-primary" data-bs-toggle="tooltip" title="Start an audio call">Start Audio Call</button>
            <button id="startVideoCall" class="btn btn-success" data-bs-toggle="tooltip" title="Start a video call">Start Video Call</button>
            <button id="screenShare" class="btn btn-warning" data-bs-toggle="tooltip" title="Share your screen">Share Screen</button>
            <button id="endCall" class="btn btn-danger" data-bs-toggle="tooltip" title="End the current call">End Call</button>
        </div>
        <div>
            <video id="localVideo" autoplay muted></video>
            <video id="remoteVideo" autoplay></video>
        </div>
        <div id="chatBox"></div>
        <textarea id="chatInput" placeholder="Type your message..." class="form-control" rows="3"></textarea>
        <button id="sendMessage" class="btn btn-info">Send</button>
        <button id="emojiButton" class="btn btn-secondary"><i class="far fa-smile"></i></button>
        <div class="emoji-picker" id="emojiPicker">
            <span class="emoji" data-emoji="😊">😊</span>
            <span class="emoji" data-emoji="😂">😂</span>
            <span class="emoji" data-emoji="😢">😢</span>
            <span class="emoji" data-emoji="😡">😡</span>
            <span class="emoji" data-emoji="❤️">❤️</span>
            <!-- Add more emojis as needed -->
        </div>
        <div class="mt-3">
            <h5>Quick Links</h5>
            <ul>
                <li><a href="faq.php" class="text-dark">Frequently Asked Questions</a></li>
                <li><a href="help-docs.php" class="text-dark">Help Documentation</a></li>
                <li><a href="contact.php" class="text-dark">Contact Us</a></li>
            </ul>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tooltip initialization
        $(function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });

        let localStream;
        let remoteStream;
        let peerConnection;

        const servers = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' } // Google's public STUN server
            ]
        };

        document.getElementById('startAudioCall').onclick = startAudioCall;
        document.getElementById('startVideoCall').onclick = startVideoCall;
        document.getElementById('screenShare').onclick = startScreenShare;
        document.getElementById('endCall').onclick = endCall;

        async function startAudioCall() {
            localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            console.log("Audio call started");
        }

        async function startVideoCall() {
            localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            document.getElementById('localVideo').srcObject = localStream;
            console.log("Video call started");
        }

        async function startScreenShare() {
            try {
                const screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true });
                document.getElementById('localVideo').srcObject = screenStream;
                console.log("Screen sharing started");
            } catch (err) {
                console.error("Error starting screen share: ", err);
            }
        }

        function endCall() {
            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
            }
            if (peerConnection) {
                peerConnection.close();
                peerConnection = null;
            }
            console.log("Call ended");
        }

        document.getElementById('sendMessage').onclick = function() {
            const message = document.getElementById('chatInput').value;
            if (message) {
                document.getElementById('chatBox').innerHTML += `<div class='message my-message'>${message}</div>`;
                document.getElementById('chatInput').value = ''; // Clear input
                // Logic to send the message to the other user
            }
        };

        // Emoji picker functionality
        document.getElementById('emojiButton').onclick = function() {
            const picker = document.getElementById('emojiPicker');
            picker.style.display = picker.style.display === 'none' || picker.style.display === '' ? 'block' : 'none';
        };

        document.querySelectorAll('.emoji').forEach(emoji => {
            emoji.onclick = function() {
                const chatInput = document.getElementById('chatInput');
                chatInput.value += this.getAttribute('data-emoji'); // Add emoji to input
                document.getElementById('emojiPicker').style.display = 'none'; // Hide the emoji picker
            };
        });

        // Automatically close the emoji picker if clicked outside
        window.onclick = function(event) {
            if (!event.target.matches('#emojiButton')) {
                const picker = document.getElementById('emojiPicker');
                if (picker.style.display === 'block') {
                    picker.style.display = 'none';
                }
            }
        };
    </script>
</body>
</html>
