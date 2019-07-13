<?php


namespace App\Utils;


use GuzzleHttp\Client;
use Illuminate\Http\Request;

class YouTubeAPI
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var
     */
    protected $response;

    /**
     * YouTubeAPI constructor.
     * @param Request $request
     */
    public function __construct(Request $request, Client $client)
    {
        $this->request = $request;
        $this->client = $client;
    }

    /**
     * Send YouTube API request to retrieve info about track in the request.
     *
     * @throws \Exception
     */
    public function sendSearchRequest()
    {
        // Get original file name
        $fileNameWithExtension = $this->request->file('track')->getClientOriginalName();
        $fileName = explode('.', $fileNameWithExtension)[0];

        // Make API request
        $response = $this->client->get(env('YOUTUBE_API_ENDPOINT'), [
            'query' => [
                'part' => 'snippet',
                'maxResults' => 5,
                'order' => 'viewCount',
                'q' => $fileName,
                'key' => env('YOUTUBE_API_KEY')
            ]
        ]);

        if ($response->getStatusCode() == 200) {
            $content = json_decode($response->getBody()->getContents());

            // Track info
            $this->response = $content->items[0]->snippet;

        } else {
            throw new \Exception('Response status is not 200');
        }
    }

    /**
     * Return track title from API response.
     *
     * @return string
     */
    public function getTItle()
    {
        return $this->response->title;
    }

    /**
     * Return track image url from API response.
     *
     * @return string
     */
    public function getImage()
    {
        return $this->response->thumbnails->high->url;
    }
}