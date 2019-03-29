# gif-countdown
Symfony 4 app. Generates a countdown gif-image

A small application containing the form to set up options for a GIF countdown to be inserted into an email and the callback to generate the GIF. The image is not stored anywhere, it is a server response for the request sent by the user opening the email. The form returns an embed code generating the image (triggering the callback).