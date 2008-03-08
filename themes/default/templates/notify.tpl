Dear { $slot_receiver },

{ $slot_sender} has requested an filesharing slot for you.
Follow the instructions below on how you can use it:

* FTP access

Use your browser or your favourite FTP client to access the FTP
server with the following parameters:

  - Server: { $slot_servername }
  - User: anonymous
  - Pass: Your-email-address
  - Directory: /{ $slot_hash }

 alternativley you can click on the link below.

 ftp://{ $slot_servername }/{ $slot_hash }

* WebDAV access

If you are using a WebDAV compatible browser, you can also upload
files via the HTTP protocol.

 http://{ $slot_servername }/{ $slot_hash }

Best Regards,
Nephthys
