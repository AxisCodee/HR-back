<?php

namespace App\Http\Controllers\API;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use App\Services\GmailService;
use Google\Client;
use Google_Service_Gmail_BatchDeleteMessagesRequest;
use Google_Service_Gmail_ModifyMessageRequest;
use Google\Service\Gmail;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GmailController extends Controller
{
    protected $gmailService;
    public function __construct(GmailService $gmailService)
    {
        $this->gmailService = $gmailService;
    }
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
        $authCode = urldecode($request->auth_code);
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
                'Your email is not exist! ,Please Register with your Gmail account.',
                null,
            );
        }
        $user->google_access_token_json = json_encode($accessToken);
        $user->provider_id = $userFromGoogle->id;
        $user->save();
        $user->createToken("Google")->accessToken;
        return ResponseHelper::success('access token updated succsessfuly.', null, Response::HTTP_OK);
    }
    public function getUserInfo() //service Done !!
    {
        try {
            $user = User::find(Auth::id());
            // Create a new instance of the Google API client
            $client = $this->getClient();
            //refresh the access token if expired
            $this->gmailService->refreshAccessToken($user, $client);
            // Create a new Gmail service using the authenticated client
            $service = new \Google\Service\Oauth2($client);
            // Use the Gmail service to retrieve user info
            $userInfo = $service->userinfo->get();
            return ResponseHelper::success($userInfo, null, Response::HTTP_OK);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function getMessageById(Request $request)
    {
        try {
            $client = $this->getClient();
            $user = User::find(Auth::id());
            $this->gmailService->refreshAccessToken($user, $client);
            $service = new Gmail($client);
            $messageId = $request->messageId;
            if (!$messageId) {
                return ResponseHelper::error('Message id could not be null.', null);
            }
            $user = 'me'; // 'me' indicates the authenticated user
            $message = $service->users_messages->get($user, $request->messageId, ['format' => 'full']);
            $mail = $this->gmailService->messageFormat($message, $messageId);
            $replies = $this->gmailService->messageReplies($messageId, $service);
            return ResponseHelper::email(
                $messageId,
                $mail['senderEmail'],
                $mail['senderName'],
                $mail['gravatarUrl'],
                $mail['receiver'],
                $mail['subject'],
                $mail['messageData'],
                $mail['attachments'],
                $mail['isStarred'],
                $mail['labelStatus'],
                $mail['date'],
                $replies,
                $mail['isInbox'],
                $mail['isUnread'],
                $service = null,
                $message = 'true'
            );
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function sendEmail(Request $request)
    {
        try {
            $client = $this->getClient();
            $user = User::find(Auth::id());
            $this->gmailService->refreshAccessToken($user, $client);
            $service = new \Google\Service\Gmail($client);
            $userInfoService = new \Google\Service\Oauth2($client);
            // Use the Gmail service to retrieve user info
            $userInfo = $userInfoService->userinfo->get();
            $msg = new \Google\Service\Gmail\Message();
            return DB::transaction(function () use ($service, $msg, $request, $userInfo) {
                $msg->setRaw($this->gmailService->sendMessage($request, $userInfo));
                $sentMessage = $service->users_messages->send('me', $msg);
                // Check if the message was sent successfully
                if ($sentMessage->getId() != null) {
                    return ResponseHelper::success("Message sent successfully", null);
                }
            });
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function mail(Request $request)//mail box by type
    {
        try {
            $client = $this->getClient();
            $user = User::find(Auth::id());
            $this->gmailService->refreshAccessToken($user, $client);
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
                $messages = $this->gmailService->mailBox($results, $service);
            } while ($pageToken);
            return ResponseHelper::success($messages, null);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }
    public function search(Request $request)
    {
        try {
            $client = $this->getClient();
            $user = User::find(Auth::id());
            $this->gmailService->refreshAccessToken($user, $client);
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
                $messages = $this->gmailService->search($results, $service);
            } while ($pageToken);
            return ResponseHelper::success($messages, null);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }
    public function deleteMessages(Request $request)
    {
        try {
            $client = $this->getClient();
            $user = User::find(Auth::id());
            $this->gmailService->refreshAccessToken($user, $client);
            $service = new Gmail($client);
            // Specify the user ID (usually 'me' for the currently authenticated user)
            $userId = 'me';
            // Specify the ID of the message you want to delete
            $messageIds = $request->messageIds;
            // Create a new BatchDeleteMessagesRequest
            $batchDeleteRequest = new \Google_Service_Gmail_BatchDeleteMessagesRequest();
            $batchDeleteRequest->setIds($messageIds);
            return DB::transaction(function () use ($service, $userId, $batchDeleteRequest) {
                // Delete the messages
                $service->users_messages->batchDelete($userId, $batchDeleteRequest);
                return ResponseHelper::success(
                    'Messages deleted successfully',
                    null
                );
            });
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }
    public function starMessages(Request $request)
    {
        try {
            $client = $this->getClient();
            $user = User::find(Auth::id());
            $this->gmailService->refreshAccessToken($user, $client);
            $service = new Gmail($client);
            // Specify the user ID (usually 'me' for the currently authenticated user)
            $userId = 'me';
            // Specify the ID of the message you want to delete
            $messageIds = $request->messageIds;
            return DB::transaction(function () use ($messageIds, $userId, $service) {
                foreach ($messageIds as $messageId) {
                    $mods = new Google_Service_Gmail_ModifyMessageRequest();
                    $mods->setAddLabelIds(['STARRED']);
                    $service->users_messages->modify($userId, $messageId, $mods);
                }
                return ResponseHelper::success(
                    'Messages starred successfully',
                    null
                );
            });
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }
}
