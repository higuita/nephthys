Dear { $bucket_receiver },

{ $bucket_sender_name} has requested a file-sharing bucket for you.
Please follow the instructions below on how you can use it.

Afterwards reply to this email, so you can notify the sender that
you have transfered all files.

Best Regards,
Nephthys


----------------------------------------------------------------------


* FTP access

Use your browser or your favourite FTP client to access the FTP server
with the following parameters:

  - Server: { $bucket_servername }
  - User: anonymous
  - Pass: your-email-address
  - Directory: /{ $bucket_hash }

Alternativley, if supported by your e-mail client, you can click on the
link below to establish a connection to the FTP server:

{ $bucket_ftp_url }


----------------------------------------------------------------------


* WebDAV access

If you are using a WebDAV compatible browser, you can transfer files
via HTTP. Open the following URL in your WebDAV client.

{ $bucket_http_url }

If you are using Microsoft's Internet Explorer, you can use the following
link to directly open the WebDAV folder in your browser.

{ $bucket_http_url }/webdav.html

