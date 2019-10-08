<?php

/**
 * InstaCapture by T2
 * Get photos from Instagram (only public profiles) using Curl with PHP.
 * 
 * @author tiago
 */
class InstaCapture
{

    /**
     * Profile doesn't have photos
     */
    const ERROR_PROFILE_NO_PHOTO = 1;

    /**
     * Account is private
     */
    const ERROR_PROFILE_PRIVATE = 2;

    /**
     * Error to read the profile by curl
     */
    const ERROR_PROFILE_READ = 3;

    /**
     * Profile url is empty
     */
    const ERROR_PROFILE_URL_EMPTY = 4;

    /**
     * The error code
     * @var int
     */
    protected $errorCode;

    /**
     * The error message
     * @var string
     */
    protected $errorMessage;

    /**
     * The profile url
     * @var string
     */
    protected $profileUrl;

    /**
     * Get the error code
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Get the error message
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Connect by curl on Instagram profile and return the html code
     * @return string Html code of profile
     * @throws Exception
     */
    private function getHtmlProfile()
    {
        $ch = curl_init($this->profileUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $html = curl_exec($ch);

        if (curl_error($ch)) {
            throw new Exception(curl_error($ch), self::ERROR_PROFILE_READ);
        }

        return $html;
    }

    /**
     * Get the initial photos of an Instragram profile
     * @return array List of photos
     * @throws Exception
     */
    public function getPhotos()
    {
        try {
            if (!$this->profileUrl) {
                throw new Exception("The profile url is empty.", self::ERROR_PROFILE_URL_EMPTY);
            }
            $html = $this->getHtmlProfile();
            $photoList = $this->getPhotoList($html);
        } catch (Exception $e) {
            $photoList = array();
            $this->errorCode = $e->getCode();
            $this->errorMessage = $e->getMessage();
        }

        return $photoList;
    }

    /**
     * Process the html page of profile and capture the initial photos
     * @param string $html Html code of profile
     * @return array List of photos
     * @throws Exception
     */
    private function getPhotoList($html)
    {
        $matches = array();
        $photoList = array();
        $profileSharedData = array();

        if (preg_match("#_sharedData = (\{.*});<\/script>#", $html, $matches)) {
            if ((isset($matches[1])) && (!empty($matches[1]))) {
                $profileSharedData = json_decode($matches[1], true);
            }
        }

        if (isset($profileSharedData['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media'])) {
            $timeLineMedia = $profileSharedData['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media'];

            if (!isset($timeLineMedia['count']) || ($timeLineMedia['count'] == 0)) {
                throw new Exception("This profile doesn't have any photo.", self::ERROR_PROFILE_NO_PHOTO);
            } elseif (isset($timeLineMedia['edges']) && (count($timeLineMedia['edges']) === 0)) {
                throw new Exception("This is a private account.", self::ERROR_PROFILE_NO_PHOTO);
            }

            foreach ($timeLineMedia['edges'] as $photo) {
                if (true == $photo['node']['is_video']) {
                    continue;
                }

                $photoList[] = array(
                    'shortcode' => $photo['node']['shortcode'],
                    'display_url' => $photo['node']['display_url'],
                    'thumbnail_src' => $photo['node']['thumbnail_src'],
                    'thumbnail_resources' => $photo['node']['thumbnail_resources'],
                    'dimensions' => $photo['node']['dimensions'],
                    'liked_by' => $photo['node']['edge_liked_by']['count'],
                    'comment_by' => $photo['node']['edge_media_to_comment']['count'],
                );
            }
        }

        return $photoList;
    }

    /**
     * Set a profile url
     * @param string $url
     */
    public function setProfileUrl($url)
    {
        if ($url) {
            $this->profileUrl = preg_replace("#(\/+)$#", "", $url) . "/";
        }
    }

}
