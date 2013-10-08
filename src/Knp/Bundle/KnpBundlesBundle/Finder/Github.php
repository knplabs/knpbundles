<?php

namespace Knp\Bundle\KnpBundlesBundle\Finder;

use Github\Api\Repo;
use Symfony\Component\DomCrawler\Crawler;
use Github\Client;

/**
 * Finds github repositories using the github api
 */
class Github implements FinderInterface
{
    const ENDPOINT         = 'https://github.com/search';
    const PARAMETER_QUERY  = 'q';
    const PARAMETER_START  = 'start_value';

    /**
     * {@inheritdoc}
     */
    protected function buildUrl($page)
    {
        $params = array(
            self::PARAMETER_QUERY => $this->query,
            'repo'                => null,
            'langOverride'        => null,
            'type'                => 'Repositories',
            'language'            => 'PHP',
        );

        if ($page > 1) {
            $params[self::PARAMETER_START] = $page;
        }

        return self::ENDPOINT . '?' . http_build_query($params);
    }

    /**
     * Extracts the urls from the given google results crawler
     *
     * @param  Crawler $crawler
     *
     * @return array
     */
    protected function extractPageUrls(Crawler $crawler)
    {
        return $crawler->filter('#container h3.repolist-name a')->extract('href');
    }

    /**
     * Returns the github repository extracted from the given URL
     *
     * @param  string $url
     *
     * @return string or NULL if the URL does not contain any repository
     */
    protected function extractUrlRepository($url)
    {
        if (preg_match('/https:\/\/github\.com\/(?<username>[\w\.-]+)\/(?<repository>[\w\.-]+)/', $url, $matches)) {
            return $matches['username'] . '/' . $matches['repository'];
        }

        return null;
    }

    /**
     * @var string
     */
    private $query;

    /**
     * @var integer
     */
    private $limit;

    /**
     * @var Client
     */
    private $github;

    /**
     * @param null $query
     * @param int $limit
     * @param Client $github
     */
    public function __construct($query = null, $limit = 300, Client $github)
    {
        $this->query  = $query;
        $this->limit  = $limit;
        $this->github = $github;
    }

    /**
     * Finds the repositories
     *
     * @return array
     */
    public function find()
    {
        /** @var Repo $repositoryApi */
        $repositoryApi = $this->github->api('repo');

        $repositories = array();

        $repositoriesData = $repositoryApi->find($this->query, array('language' => 'php'));
        $repositoriesData = $repositoriesData['repositories'];

        foreach ($repositoriesData as $repositoryData) {
            $repositories[] = $this->extractUrlRepository($repositoryData['url']);
        }

        return $repositories;
    }
}
