Dear { $bucket_receiver },

{ $bucket_sender} has requested an filesharing bucket for you.
Follow the instructions below on how you can use it:

* FTP access

Use your browser or your favourite FTP client to access the FTP
server with the following parameters:

  - Server: { $bucket_servername }
  - User: anonymous
  - Pass: Your-email-address
  - Directory: /{ $bucket_hash }

 alternativley you can click on the link below.

 ftp://{ $bucket_servername }/{ $bucket_hash }

* WebDAV access

If you are using a WebDAV compatible browser, you can also upload
files via the HTTP protocol.

 http://{ $bucket_servername }/{ $bucket_hash }

Best Regards,
Nephthys
