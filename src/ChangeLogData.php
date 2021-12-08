<?php

namespace Violinist\GitLogFormat;

class ChangeLogData
{

    /**
     * @var \Violinist\GitLogFormat\ChangeLogLine[]
     */
    private $lines = [];

    /**
     * @return string
     */
    public function getGitSource()
    {
        return $this->gitSource;
    }

    /**
     * @param string $gitSource
     */
    public function setGitSource($gitSource)
    {
        if ($this->gitSourceIsSupported($gitSource)) {
            $this->gitSource = $gitSource;
        }
    }

    /**
     * @var string
     */
    private $gitSource;

    protected function gitSourceIsSupported($git)
    {
        $suported_prefixes = [
            'https://github.com/',
            'https://git.drupal.org',
            'https://git.drupalcode.org'
        ];
        foreach ($suported_prefixes as $prefix) {
            if (strpos($git, $prefix) === 0) {
                return true;
            }
        }
        return false;
    }


    /**
     * @param string $changelog_string
     */
    public static function createFromString($changelog_string)
    {
        $lines = explode("\n", $changelog_string);
        $data = new static();
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }
            // Line should now be formatted like this:
            // <shorthash> <commit message>
            $line_data = explode(' ', $line);
            // So first one is now commit hash. The rest is message.
            $commit = array_shift($line_data);
            // Then implode it back without the sha.
            // @todo. This seems like it could be done faster with regex, but since I
            // am doing this huge upgrade with no internet, I can't really google
            // anything :o.
            $message = implode(' ', $line_data);
            $data->lines[] = new ChangeLogLine($commit, $message);
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getAsMarkdown()
    {
        $output = '';
        foreach ($this->lines as $line) {
            $data = $this->parseSingleLine($line);
            if (!empty($data['link'])) {
                $output .= sprintf("- [%s](%s) `%s`\n", $data['hash'], $data['link'], $data['message']);
            } else {
                $output .= sprintf("- %s `%s`\n", $data['hash'], $data['message']);
            }
        }
        return $output;
    }

    protected function parseSingleLine(ChangeLogLine $line)
    {
        $data = [
            'hash' => $line->getCommit(),
            'message' => $line->getCommitMessage(),
        ];
        if ($this->gitSource) {
            try {
                $url = $this->getCommitUrl($this->getGitSource(), $line->getCommit());
                $data['link'] = $url;
            } catch (\Exception $e) {
            }
        }
        return $data;
    }

    public function getAsJson()
    {
        $return_data = [];
        foreach ($this->lines as $line) {
            $return_data[] = $this->parseSingleLine($line);
        }
        return json_encode($return_data);
    }


    /**
     * @param $url
     * @param $commit
     *
     * @return string
     * @throws \Exception
     */
    protected function getCommitUrl($url, $commit)
    {
        $url_parsed = parse_url($url);
        switch ($url_parsed['host']) {
            case 'github.com':
                return sprintf('%s/commit/%s', $url, $commit);

            case 'git.drupalcode.org':
            case 'git.drupal.org':
                $project_name = str_replace('/project/', '', $url_parsed['path']);
                return sprintf('https://git.drupalcode.org/project/%s/commit/%s', $project_name, $commit);

            default:
                throw new \Exception('Git URL host not supported.');
        }
    }
}
