<?php


namespace Violinist\GitLogFormat\Test\Unit;

use PHPUnit\Framework\TestCase;
use Violinist\GitLogFormat\ChangeLogData;
use Violinist\GitLogFormat\Test\DummyChangeLog;

class ChangelogTest extends TestCase
{
    public function testFromString()
    {
        $data = ChangeLogData::createFromString("ababab change 1\nfefefe change 2");
        $markdown = $data->getAsMarkdown();
        $this->assertEquals('- ababab `change 1`
- fefefe `change 2`
', $markdown);
    }

    public function testWithLinks()
    {
        $data = ChangeLogData::createFromString("ababab change 1\nfefefe change 2");
        $data->setGitSource('https://github.com/violinist-dev/git-log-format');
        $markdown = $data->getAsMarkdown();
        $this->assertEquals('- [ababab](https://github.com/violinist-dev/git-log-format/commit/ababab) `change 1`
- [fefefe](https://github.com/violinist-dev/git-log-format/commit/fefefe) `change 2`
', $markdown);
    }

    public function testWithDrupalLinks()
    {
        $data = ChangeLogData::createFromString("ababab change 1\nfefefe change 2");
        $data->setGitSource('https://git.drupal.org/project/violinist_projects');
        $markdown = $data->getAsMarkdown();
        $this->assertEquals('- [ababab](https://git.drupalcode.org/project/violinist_projects/commit/ababab) `change 1`
- [fefefe](https://git.drupalcode.org/project/violinist_projects/commit/fefefe) `change 2`
', $markdown);
    }

    public function testWithNewDrupalLinks()
    {
        $data = ChangeLogData::createFromString("ababab change 1\nfefefe change 2");
        $data->setGitSource('https://git.drupalcode.org/project/violinist_projects');
        $markdown = $data->getAsMarkdown();
        $this->assertEquals('- [ababab](https://git.drupalcode.org/project/violinist_projects/commit/ababab) `change 1`
- [fefefe](https://git.drupalcode.org/project/violinist_projects/commit/fefefe) `change 2`
', $markdown);
    }

    public function testEmptyLines()
    {
        $data = ChangeLogData::createFromString("");
        $markdown = $data->getAsMarkdown();
        $this->assertEquals('', $markdown);
    }

    public function testNotSupportedUrl()
    {
        $data = ChangeLogData::createFromString('aba test test');
        $data->setGitSource('http://example.com');
        $this->assertEquals('- aba `test test`
', $data->getAsMarkdown());
    }

    /**
     * This is just so we can test what happens when we have set a URL but we can not convert it.
     */
    public function testNotSupportedUrlOverridden()
    {
        $data = DummyChangeLog::createFromString('aba test test');
        $data->setGitSource('http://example.com');
        $this->assertEquals('- aba `test test`
', $data->getAsMarkdown());
    }

    public function testGetAsJson()
    {
        $data = ChangeLogData::createFromString("ababab change 1\nfefefe change 2");
        $data->setGitSource('https://github.com/violinist-dev/git-log-format');
        $json = $data->getAsJson();
        $this->assertEquals('[{"hash":"ababab","message":"change 1","link":"https:\/\/github.com\/violinist-dev\/git-log-format\/commit\/ababab"},{"hash":"fefefe","message":"change 2","link":"https:\/\/github.com\/violinist-dev\/git-log-format\/commit\/fefefe"}]', $json);
    }
}
