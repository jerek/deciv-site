<?php

require_once '../library/Podcast.php';

$feed = Podcast::$feed;
$episodes = Podcast::getEpisodes();

header('Content-Type: application/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="utf-8" ?>', "\n";

?>
<rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" version="2.0">
    <channel>
        <title><?=$feed['title']?></title>
        <link><?=$feed['link']?></link>

        <?php /* iTunes-specific metadata */ ?>

        <itunes:author><?=$feed['author']?></itunes:author>
        <itunes:owner>
            <itunes:name><?=$feed['author']?></itunes:name>
            <itunes:email><?=$feed['email']?></itunes:email>
        </itunes:owner>
        <itunes:image href="<?=$feed['image']?>" />
        <itunes:explicit><?=$feed['explicit']?></itunes:explicit>
        <itunes:category text="<?=$feed['category']?>">
            <itunes:category text="<?=$feed['subcategory']?>" />
        </itunes:category>
        <itunes:summary><?=$feed['description']?></itunes:summary>

        <?php /* Non-iTunes metadata */ ?>

        <category>Music</category>
        <description><?=$feed['description']?></description>
        <language><?=$feed['lang']?></language>
        <copyright><?=$feed['copyright']?></copyright>
        <ttl><?=$feed['ttl']?></ttl>

        <?php /* The episodes*/ ?>

        <?php foreach ($episodes as $episode): ?>

        <item>
            <title><?=$episode['title']?></title>
            <link><?=$episode['url']?></link>

            <itunes:author><?=$episode['author']?></itunes:author>
            <itunes:category text="<?=$feed['category']?>">
                <itunes:category text="<?=$feed['subcategory']?>" />
            </itunes:category>

            <category>Music</category>
            <duration><?=$episode['duration']?></duration>

            <description><?=$episode['description']?></description>
            <pubDate><?=$episode['date']?></pubDate>

            <enclosure url="<?=$episode['url']?>" length="<?=$episode['size']?>" type="audio/mpeg" />
            <guid><?=$episode['url']?></guid>
            <author><?=$feed['email']?></author>
        </item>

        <?php endforeach; ?>

    </channel>
</rss>
