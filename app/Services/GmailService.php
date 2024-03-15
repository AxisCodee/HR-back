<?php

namespace App\Services;


use App\Helper\ResponseHelper;
use App\Models\User;
use Google\Client as GoogleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class GmailService
{
    public function refreshAccessToken(User $user, GoogleClient $client)
    {
        $tokenData = json_decode($user->google_access_token_json, true);
        // Extract the access token
        $accessToken = $tokenData['access_token'];
        $refreshToken = $tokenData['refresh_token'];
        // Now $accessToken contains the Google access token
        $userAccessToken = urldecode($accessToken);
        // Set the access token obtained for the user
        $client->setAccessToken($userAccessToken);
        if ($client->isAccessTokenExpired()) {
            $at = $client->fetchAccessTokenWithRefreshToken($refreshToken);
            $client->getRefreshToken();
            $client->setAccessToken($client->getAccessToken());
            $user->google_access_token_json = json_encode($at);
            $user->save();
        }
        return "access token updated";
    }
    public function messageFormat($message, $messageId)
    {
        $mail = [];
        $payload = $message->getPayload();
        // Sender info
        $headers = $payload['headers'];
        $fromHeader = Arr::first($headers, function ($header) {
            return $header['name'] === 'From';
        });
        $fromValue = $fromHeader['value'];
        preg_match('/^(.*)<(.*)>$/', $fromValue, $matches);
        $senderName = $matches[1];
        $senderEmail = $matches[2];
        $gravatarUrl = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($senderEmail)));
        $mail['senderName'] = $senderName;
        $mail['senderEmail'] = $senderEmail;
        $mail['gravatarUrl'] = $gravatarUrl;
        // Receiver info
        $toHeader = Arr::first($headers, function ($header) {
            return $header['name'] === 'Delivered-To';
        });
        $toValue = $toHeader['value'];
        $mail['receiver'] = $toValue;
        // Subject
        $subjectHeader = Arr::first($headers, function ($header) {
            return $header['name'] === 'Subject';
        });
        $subjectValue = $subjectHeader['value'];
        $mail['subject'] = $subjectValue;
        // CC
        // BCC
        // Body
        $body = $payload->getBody();
        $messageData = $this->parseMessageParts($message['payload']['parts']);
        $mail['messageData'] = $messageData;
        // Attachments
        $attachments = $this->messageAttachments($payload, $messageId);
        $mail['attachments'] = $attachments;
        // Labels
        $labelIds = $message['labelIds'] ?? [];
        $mail['labelStatus'] = [
            'isPROMOTIONS' => in_array('CATEGORY_PROMOTIONS', $labelIds),
            'isPERSONAL' => in_array('CATEGORY_PERSONAL', $labelIds),
            'isSOCIAL' => in_array('CATEGORY_SOCIAL', $labelIds)
        ];
        // Date
        $dateHeader = Arr::first($headers, function ($header) {
            return $header['name'] === 'Date';
        });
        $dateValue = $dateHeader['value'];
        $mail['date'] = $dateValue;
        // Folder (Inbox)
        $isInbox = in_array('INBOX', $labelIds);
        $mail['isInbox'] = $isInbox;
        // Read
        $isUnread = in_array('UNREAD', $labelIds);
        $mail['isUnread'] = $isUnread;
        $isStarred = in_array('STARRED', $labelIds);
        $mail['isStarred'] = $isStarred;
        return $mail;
    }


    public function messageReplies($messageId, $service)
    {
        $threadId = $messageId; // Replace with the actual thread ID
        $thread = $service->users_threads->get('me', $threadId);
        $messages = $thread->getMessages();
        $replies = [];
        foreach ($messages as $message) {
            if ($message->getId() !== $messageId) {
                $replies[] = [
                    'id' => $message->getId(),
                    'snippet' => $message->getSnippet(),
                ];
            }
        }
        return $replies;
    }

    public function messageAttachments($payload, $messageId)
    {
        $attachments = [];
        $parts = $payload['parts'] ?? null;
        if ($parts) {
            foreach ($parts as $part) {
                if (isset($part['body']['attachmentId'])) {
                    $attachmentId = $part['body']['attachmentId'];
                    $filename = $part['filename'];
                    $size = $part['body']['size'];
                    // Construct the thumbnail URL based on the file type
                    $thumbnail = ''; // You may need to handle different file types differently
                    // Construct the URL to download the attachment
                    $attachmentUrl = "https://www.googleapis.com/gmail/v1/users/me/messages/{$messageId}/attachments/{$attachmentId}";
                    $attachmentDetails = [
                        'filename' => $filename,
                        'thumbnail' => $thumbnail,
                        'url' => $attachmentUrl,
                        'size' => $size
                    ];
                    $attachments[] = $attachmentDetails;
                }
            }
        }
        return $attachments;
    }

    public function parseMessageParts($parts)
    {
        $data = [
            'text/plain' => '',
            'text/html' => ''
        ];
        foreach ($parts as $part) {
            if ($part['mimeType'] === 'text/plain') {
                $data['text/plain'] = $part['body']['data'];
            } elseif ($part['mimeType'] === 'text/html') {
                $data['text/html'] = $part['body']['data'];
            } elseif ($part['mimeType'] === 'multipart/alternative') {
                $data = array_merge($data, $this->parseMessageParts($part['parts']));
            }
        }
        return $data;
    }


    public function sendMessage($request, $userInfo)
    {
        $strSubject = $request->subject;
        $strRawMessage = "From: $userInfo->name <$userInfo->email>\r\n";
        $strRawMessage .= "To: <$request->recieverEmail>\r\n";
        $strRawMessage .= 'Subject: =?utf-8?B?' . base64_encode($strSubject) . "?=\r\n";
        $strRawMessage .= "MIME-Version: 1.0\r\n";
        $strRawMessage .= "Content-Type: multipart/mixed; boundary=foo_bar_baz\r\n";
        $strRawMessage .= "\r\n";
        $strRawMessage .= "--foo_bar_baz\r\n";
        $strRawMessage .= "Content-Type: text/plain\r\n\r\n";
        $strRawMessage .= "$request->content\r\n";
        // Check if there is an uploaded file
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachment = chunk_split(base64_encode(file_get_contents($file->getRealPath())));
            $strRawMessage .= "--foo_bar_baz\r\n";
            $strRawMessage .= "Content-Type: application/octet-stream; name=" . $file->getClientOriginalName() . "\r\n" .
                "Content-Transfer-Encoding: base64\r\n" .
                "Content-Disposition: attachment; filename=" . $file->getClientOriginalName() . "\r\n\r\n" .
                $attachment . "\r\n";
        }
        $strRawMessage .= "--foo_bar_baz--";
        // The message needs to be encoded in Base64URL
        $mime = rtrim(strtr(base64_encode($strRawMessage), '+/', '-_'), '=');
        return $mime;
    }

    public function mailBox($results, $service)
    {
        $messages = [];
        foreach ($results->getMessages() as $email) {
            $message = $service->users_messages->get('me', $email->getId(), ['format' => 'metadata', 'metadataHeaders' => ['From', 'To', 'Subject', 'Date']]);
            $headers = $message->getPayload()->getHeaders();
            $data = [
                'id' => $email->getId(),
                'sender' => '',
                'senderImage' => '', // New key for sender's image
                'subject' => '',
                'date' => '',
                'isStarred' => in_array('STARRED', $message->getLabelIds())
            ];
            foreach ($headers as $header) {
                if ($header->getName() == 'id') {
                    $data['id'] = $header->getValue();
<<<<<<< HEAD
=======
                    dd(1);
>>>>>>> ca07891d68b98b8ebb9be025a988ecbe4cb5ee46
                }
                if ($header->getName() == 'From') {
                    $data['sender'] = $header->getValue();
                    $senderEmail = $header->getValue();
                    preg_match('/^(.*)<(.*)>$/', $senderEmail, $matches);
                    $senderEmail = $matches[2];
<<<<<<< HEAD

=======
>>>>>>> ca07891d68b98b8ebb9be025a988ecbe4cb5ee46
                    // Fetch Gravatar image URL
                    $gravatarUrl = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($senderEmail)));
                    $data['senderImage'] = $gravatarUrl;
                }
                if ($header->getName() == 'Subject') {
                    $data['subject'] = $header->getValue();
                }
                if ($header->getName() == 'Date') {
                    $data['date'] = $header->getValue();
                }
            }
<<<<<<< HEAD
=======

>>>>>>> ca07891d68b98b8ebb9be025a988ecbe4cb5ee46
            $messages[] = $data;
        }
        return $messages;
    }

    public function search($results, $service)
    {
        $messages = [];
        foreach ($results->getMessages() as $email) {
            $message = $service->users_messages->get('me', $email->getId(), ['format' => 'metadata', 'metadataHeaders' => ['From', 'To', 'Subject', 'Date']]);
            $headers = $message->getPayload()->getHeaders();
            $data = [
                'id' => $email->getId(),
                'sender' => '',
                'subject' => '',
                'date' => '',
                'isStarred' => in_array('STARRED', $message->getLabelIds())
            ];
            foreach ($headers as $header) {
                if ($header->getName() == 'id') {
                    $data['id'] = $header->getValue();
                }
                if ($header->getName() == 'From') {
                    $data['sender'] = $header->getValue();
                }
                if ($header->getName() == 'Subject') {
                    $data['subject'] = $header->getValue();
                }
                if ($header->getName() == 'Date') {
                    $data['date'] = $header->getValue();
                }
            }
            $messages[] = $data;
        }
        return $messages;
    }
}
