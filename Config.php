<?php

$config = array
(
	// Defined by Payment Service Provider standard.
	// Information MUST be supplied by application using PSP interface.

	"DebugMail"			=> "debug@example.com",								// Set e-mail address to enable debugging - any errors are sent to this address (mail(..) must be enabled) - do NOT use in production!
	"EncryptionKey"		=> "-mjhf6/43kBSD&24*f.GL;4917fd@DMBv_IQ512",		// Random value used to encrypt data to prevent man-in-the-middle attacks
	"BasePath"			=> "PSP",											// Folder relative to application containing PSP package (e.g. libs/PSP)
	"BaseUrl"			=> "http://example.com/libs/PSP"					// External URL to folder containing PSP package (e.g. http://example.com/libs/PSP)
);

?>
