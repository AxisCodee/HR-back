<?php

namespace App\Http\Controllers\API;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use Google\Client;
use Google_Service_Gmail_BatchDeleteMessagesRequest;
use Google_Service_Gmail_ModifyMessageRequest;
use Google\Service\Gmail;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class GmailController extends Controller
{
    /**
     * Gets a google client
     *
     * @return \Google_Client
     * INCOMPLETE
     */
    private function getClient(): \Google_Client
    {
        // load our config.json that contains our credentials for accessing google's api as a json string
        $configJson = base_path() . '/config.json';
        // define an application name
        $applicationName = 'myfancyapp';
        // create the client
        $client = new \Google_Client();
        $client->setApplicationName($applicationName);
        $client->setAuthConfig($configJson);
        $client->setAccessType('offline'); // necessary for getting the refresh token
        $client->setPrompt('consent'); // necessary for getting the refresh token
        // scopes determine what google endpoints we can access. keep it simple for now.
        $client->setScopes(
            [
                \Google\Service\Oauth2::USERINFO_PROFILE,
                \Google\Service\Oauth2::USERINFO_EMAIL,
                \Google\Service\Oauth2::OPENID,
                \Google\Service\Gmail::GMAIL_READONLY,
                \Google\Service\Gmail::MAIL_GOOGLE_COM,
            ]
        );
        $client->setIncludeGrantedScopes(true);
        return $client;
    } // getClient
    /**
     * Return the url of the google auth.
     * FE should call this and then direct to this url.
     *
     * @return JsonResponse
     * INCOMPLETE
     */
    public function getAuthUrl(Request $request)
    {
        /**
         * Create google client
         */
        $client = $this->getClient();
        /**
         * Generate the url at google we redirect to
         */
        $authUrl = $client->createAuthUrl();

        /**
         * HTTP 200
         */
        return ResponseHelper::success($authUrl, null, Response::HTTP_OK);
    }

    public function postLogin(Request $request)
    {
        /**
         * Get authcode from the query string
         * Url decode if necessary
         */
        $authCode = urldecode($request->input('auth_code'));
        /**
         * Google client
         */
        $client = $this->getClient();
        /**
         * Exchange auth code for access token
         * Note: if we set 'access type' to 'force' and our access is 'offline', we get a refresh token. we want that.
         */
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        /**
         * Set the access token with google. nb json
         */
        $client->setAccessToken(json_encode($accessToken));
        /**
         * Get user's data from google
         */
        $service = new \Google\Service\Oauth2($client);
        $userFromGoogle = $service->userinfo->get();
        $user = User::where('email', '=', $userFromGoogle['email'])
            ->first();
        if (!$user) {
            return ResponseHelper::error(
                'Your email is not exist! ,Register with your gmail account.',
                null,
            );
        }
        $user->google_access_token_json = json_encode($accessToken);
        $user->provider_id = $userFromGoogle->id;
        $user->save();
        $token = $user->createToken("Google")->accessToken;
        return ResponseHelper::success('access token updated succsessfuly.', null, Response::HTTP_OK);
    }
    public function getUserInfo(Request $request)
    {
        $user = User::find(Auth::id()); //replace with auth
        // Access the 'google_access_token_json' column from the user model
        $jsonData = $user->google_access_token_json;
        // Decode the JSON data
        $tokenData = json_decode($jsonData, true);
        // Extract the access token
        $accessToken = $tokenData['access_token'];
        $refreshToken = $tokenData['refresh_token'];
        // Now $accessToken contains the Google access token
        //dd($refreshToken);
        $userAccessToken = urldecode($accessToken);
        // Create a new instance of the Google API client
        $client = $this->getClient();
        // Set the access token obtained for the user
        $client->setAccessToken($userAccessToken); // Replace $userAccessToken with the actual user's access token
        if ($client->isAccessTokenExpired()) {
            $at = $client->fetchAccessTokenWithRefreshToken($refreshToken);
            $client->getRefreshToken();
            $client->setAccessToken($client->getAccessToken());
            $user->google_access_token_json = json_encode($at);
            $user->save();
        }
        // Create a new Gmail service using the authenticated client
        $service = new \Google\Service\Oauth2($client);
        // Use the Gmail service to retrieve user info
        $userInfo = $service->userinfo->get();
        return ResponseHelper::success($userInfo, null, Response::HTTP_OK);
    }
    public function getMessageById(Request $request)
    {
        $client = $this->getClient();
        $user = User::find(Auth::id());
        // Access the 'google_access_token_json' column from the user model
        $jsonData = $user->google_access_token_json;
        // Decode the JSON data
        $tokenData = json_decode($jsonData, true);
        // Extract the access token
        $accessToken = $tokenData['access_token'];
        $refreshToken = $tokenData['refresh_token'];
        // Now $accessToken contains the Google access token
        $userAccessToken = urldecode($accessToken);
        // Create a new instance of the Google API client
        $client = $this->getClient();
        // Set the access token obtained for the user
        $client->setAccessToken($userAccessToken); // Replace $userAccessToken with the actual user's access token
        if ($client->isAccessTokenExpired()) {
            $at = $client->fetchAccessTokenWithRefreshToken($refreshToken);
            $client->getRefreshToken();
            $client->setAccessToken($client->getAccessToken());
            $user->google_access_token_json = json_encode($at);
            $user->save();
        }
        $service = new Gmail($client);
        $messageId = $request->messageId;
        if (!$messageId) {
            return ResponseHelper::error('Message id could not be null.', null);
        }
        $user = 'me'; // 'me' indicates the authenticated user
        try {
            $message = $service->users_messages->get($user, $request->messageId, ['format' => 'full']);
            $payload = $message->getPayload();
            // dd($message);
            //sender info
            $headers = $payload['headers'];
            $fromHeader = Arr::first($headers, function ($header) {
                return $header['name'] === 'From';
            });
            $fromValue = $fromHeader['value']; // This will be "Ashampoo News <info@news.ashampoo.com>"
            preg_match('/^(.*)<(.*)>$/', $fromValue, $matches);
            $senderName = $matches[1]; // This will be "Ashampoo News"
            $senderEmail = $matches[2]; // This will be "info@news.ashampoo.com"
            $gravatarUrl = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($senderEmail)));
            //reciever info
            $headers = $payload['headers'];
            $toHeader = Arr::first($headers, function ($header) {
                return $header['name'] === 'Delivered-To';
            });
            $toValue = $toHeader['value'];
            //subject
            $headers = $payload['headers'];
            $subjectHeader = Arr::first($headers, function ($header) {
                return $header['name'] === 'Subject';
            });
            $subjectValue = $subjectHeader['value'];
            //cc
            //bc
            //body
            $body = $payload->getBody();
            function parseParts($parts)
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
                        $data = array_merge($data, parseParts($part['parts']));
                    }
                }
                return $data;
            }
            $messageData = parseParts($message['payload']['parts']);
            //attachments
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
            //lables
            $labelIds = $message['labelIds'] ?? [];
            $isStarred = in_array('STARRED', $labelIds);
            $isPROMOTIONS = in_array('CATEGORY_PROMOTIONS', $labelIds);
            $isPERSONAL = in_array('CATEGORY_PERSONAL', $labelIds);
            $isSOCIAL = in_array('CATEGORY_SOCIAL', $labelIds);
            // Initialize the array to hold the merged values
            $labelStatus = [];
            // Check if each label exists in the labelIds array and assign the result to the corresponding key
            $labelStatus['isPROMOTIONS'] = in_array('CATEGORY_PROMOTIONS', $labelIds);
            $labelStatus['isPERSONAL'] = in_array('CATEGORY_PERSONAL', $labelIds);
            $labelStatus['isSOCIAL'] = in_array('CATEGORY_SOCIAL', $labelIds);
            //Date
            $headers = $payload['headers'];
            $dateHeader = Arr::first($headers, function ($header) {
                return $header['name'] === 'Date';
            });
            $dateValue = $dateHeader['value'];
            //replies
            $threadId = $messageId; // Replace with the actual thread ID
            $thread = $service->users_threads->get('me', $threadId);
            $messages = $thread->getMessages();
            $replies = [];
            foreach ($messages as $message) {
                if ($message->getId() !== $messageId) {
                    $replies[] = [
                        'id' => $message->getId(),
                        'snippet' => $message->getSnippet(),
                        // Add any other relevant information you need
                    ];
                }
            }
            //folder (inbox)
            $isInbox = in_array('INBOX', $labelIds);
            //readen
            $isUnread = in_array('UNREAD', $labelIds);
            return ResponseHelper::success([
                'emails' => [
                    [
                        'id' => $messageId,
                        'from' => [
                            'email' => $senderEmail,
                            'name' => $senderName,
                            'avatar' => $gravatarUrl
                        ],
                        'to' => [
                            [
                                'name' => 'me',
                                'email' => $toValue
                            ]
                        ],
                        'subject' => $subjectValue,
                        'cc' => [],
                        'bcc' => [],
                        'message' => $messageData,
                        'attachments' => $attachments,
                        'isStarred' => $isStarred,
                        'labels' => $labelStatus,
                        'time' => $dateValue,
                        'replies' => $replies,
                        'folder' => $isInbox ? 'inbox' : '',
                        'isRead' => !$isUnread
                    ]
                ]
            ], null);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur
            return ResponseHelper::error(
                $e->getMessage(),
                null,
            );
        }
    }
    public function sendEmail(Request $request)
    {
        $client = $this->getClient();
        $user = User::find(Auth::id());
        // Access the 'google_access_token_json' column from the user model
        $jsonData = $user->google_access_token_json;
        // Decode the JSON data
        $tokenData = json_decode($jsonData, true);
        // Extract the access token
        $accessToken = $tokenData['access_token'];
        $refreshToken = $tokenData['refresh_token'];
        // Now $accessToken contains the Google access token
        $userAccessToken = urldecode($accessToken);
        // Create a new instance of the Google API client
        $client = $this->getClient();
        // Set the access token obtained for the user
        $client->setAccessToken($userAccessToken);
        //dd($client->isAccessTokenExpired());
        if ($client->isAccessTokenExpired()) {
            $at = $client->fetchAccessTokenWithRefreshToken($refreshToken);
            $client->getRefreshToken();
            $client->setAccessToken($client->getAccessToken());
            $user->google_access_token_json = json_encode($at);
            $user->save();
        }
        $service = new \Google\Service\Gmail($client);
        $userInfoService = new \Google\Service\Oauth2($client);
        // Use the Gmail service to retrieve user info
        $userInfo = $userInfoService->userinfo->get();
        // Define the email parameters
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
        $msg = new \Google\Service\Gmail\Message();
        $msg->setRaw($mime);
        // Send the message
        $sentMessage = $service->users_messages->send('me', $msg);
        // Check if the message was sent successfully
        if ($sentMessage->getId() != null) {
            return ResponseHelper::success("Message sent successfully", null);
        } else {
            return ResponseHelper::error("Message not sent", null);
        }
    }
    public function mail(Request $request)
    {
        $client = $this->getClient();
        $user = User::find(Auth::id());
        // Access the 'google_access_token_json' column from the user model
        $jsonData = $user->google_access_token_json;
        // Decode the JSON data
        $tokenData = json_decode($jsonData, true);
        // Extract the access token
        $accessToken = $tokenData['access_token'];
        $refreshToken = $tokenData['refresh_token'];
        // Now $accessToken contains the Google access token
        $userAccessToken = urldecode($accessToken);
        // Create a new instance of the Google API client
        $client = $this->getClient();
        // Set the access token obtained for the user
        $client->setAccessToken($userAccessToken);
        if ($client->isAccessTokenExpired()) {
            $at = $client->fetchAccessTokenWithRefreshToken($refreshToken);
            $client->getRefreshToken();
            $client->setAccessToken($client->getAccessToken());
            $user->google_access_token_json = json_encode($at);
            $user->save();
        }
        $service = new Gmail($client);
        $user = 'me'; // 'me' indicates the authenticated user
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        // Assuming $service is an instance of Google\Service\Gmail
        $pageToken = NULL;
        $messages = [];
        do {
            $optParams = [
                'labelIds' => $request->boxType,
                'pageToken' => $pageToken
            ];
            $results = $service->users_messages->listUsersMessages('me', $optParams);
            if ($results->getNextPageToken()) {
                $pageToken = $results->getNextPageToken();
            } else {
                $pageToken = NULL;
            }
            if (!$results->getMessages()) {
                return response()->json(['The Folder is empty.']);
            }
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
        } while ($pageToken);
        return ResponseHelper::success($messages, null);
    }
    public function search(Request $request)
    {
        $client = $this->getClient();
        $user = User::find(Auth::id());
        // Access the 'google_access_token_json' column from the user model
        $jsonData = $user->google_access_token_json;
        // Decode the JSON data
        $tokenData = json_decode($jsonData, true);
        // Extract the access token
        $accessToken = $tokenData['access_token'];
        $refreshToken = $tokenData['refresh_token'];
        // Now $accessToken contains the Google access token
        $userAccessToken = urldecode($accessToken);
        // Create a new instance of the Google API client
        $client = $this->getClient();
        // Set the access token obtained for the user
        $client->setAccessToken($userAccessToken);
        if ($client->isAccessTokenExpired()) {
            $at = $client->fetchAccessTokenWithRefreshToken($refreshToken);
            $client->getRefreshToken();
            $client->setAccessToken($client->getAccessToken());
            $user->google_access_token_json = json_encode($at);
            $user->save();
        }
        $service = new Gmail($client);
        $user = 'me'; // 'me' indicates the authenticated user
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        // Assuming $service is an instance of Google\Service\Gmail
        $pageToken = NULL;
        $messages = [];
        do {
            $optParams = [
                'q' => $request->searchQuery,
                'pageToken' => $pageToken
            ];
            $results = $service->users_messages->listUsersMessages('me', $optParams);
            if ($results->getNextPageToken()) {
                $pageToken = $results->getNextPageToken();
            } else {
                $pageToken = NULL;
            }
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
        } while ($pageToken);
        return ResponseHelper::success($messages, null);
    }
    public function deleteMessages(Request $request)
    {
        $client = $this->getClient();
        $user = User::find(Auth::id());
        // Access the 'google_access_token_json' column from the user model
        $jsonData = $user->google_access_token_json;
        // Decode the JSON data
        $tokenData = json_decode($jsonData, true);
        // Extract the access token
        $accessToken = $tokenData['access_token'];
        $refreshToken = $tokenData['refresh_token'];
        // Now $accessToken contains the Google access token
        $userAccessToken = urldecode($accessToken);
        // Create a new instance of the Google API client
        $client = $this->getClient();
        // Set the access token obtained for the user
        $client->setAccessToken($userAccessToken);
        if ($client->isAccessTokenExpired()) {
            $at = $client->fetchAccessTokenWithRefreshToken($refreshToken);
            $client->getRefreshToken();
            $client->setAccessToken($client->getAccessToken());
            $user->google_access_token_json = json_encode($at);
            $user->save();
        }
        $service = new Gmail($client);
        // Specify the user ID (usually 'me' for the currently authenticated user)
        $userId = 'me';
        // Specify the ID of the message you want to delete
        $messageIds = $request->messageIds;
        try {
            // Create a new BatchDeleteMessagesRequest
            $batchDeleteRequest = new Google_Service_Gmail_BatchDeleteMessagesRequest();
            $batchDeleteRequest->setIds($messageIds);
            // Delete the messages
            $service->users_messages->batchDelete($userId, $batchDeleteRequest);
            return ResponseHelper::success(
                'Messages deleted successfully'
                ,
                null
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(
                $e->getMessage(),
                null,
            );
        }
    }
    public function starMessages(Request $request)
    {
        $client = $this->getClient();
        $user = User::find(Auth::id());
        // Access the 'google_access_token_json' column from the user model
        $jsonData = $user->google_access_token_json;
        // Decode the JSON data
        $tokenData = json_decode($jsonData, true);
        // Extract the access token
        $accessToken = $tokenData['access_token'];
        $refreshToken = $tokenData['refresh_token'];
        // Now $accessToken contains the Google access token
        $userAccessToken = urldecode($accessToken);
        // Create a new instance of the Google API client
        $client = $this->getClient();
        // Set the access token obtained for the user
        $client->setAccessToken($userAccessToken);
        if ($client->isAccessTokenExpired()) {
            $at = $client->fetchAccessTokenWithRefreshToken($refreshToken);
            $client->getRefreshToken();
            $client->setAccessToken($client->getAccessToken());
            $user->google_access_token_json = json_encode($at);
            $user->save();
        }
        $service = new Gmail($client);
        // Specify the user ID (usually 'me' for the currently authenticated user)
        $userId = 'me';
        // Specify the ID of the message you want to delete
        $messageIds = $request->messageIds;
        try {
            foreach ($messageIds as $messageId) {
                $mods = new Google_Service_Gmail_ModifyMessageRequest();
                $mods->setAddLabelIds(['STARRED']);
                $service->users_messages->modify($userId, $messageId, $mods);
            }
            return ResponseHelper::success(
                'Messages starred successfully'
                ,
                null
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(
                $e->getMessage(),
                null,
            );
        }
    }
}
