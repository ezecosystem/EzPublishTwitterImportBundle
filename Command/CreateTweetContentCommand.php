<?php

namespace xrow\EzPublishTwitterImportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreateTweetContentCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName( 'import:twitter' )
            ->setDescription( "Creates a new Content of the Tweets type" )
            ->setDefinition(
                array(
                    new InputArgument( 'location', InputArgument::REQUIRED, 'An existing parent location (node) id' ),
                    new InputArgument( 'twitterAccount', InputArgument::IS_ARRAY|InputArgument::REQUIRED, 'The Twitter name' )
                )
            );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $locationId = $input->getArgument( 'location' );
        $twitterAccounts = $input->getArgument( 'twitterAccount' );
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $repository->setCurrentUser(
            $repository->getUserService()->loadUserByLogin( 'xxxxx' )
        );

        $contentService = $repository->getContentService();
        
        //Getting a twitter user's latest posts using Guzzle
        $twitterClient = $this->getApplication()->getKernel()->getContainer()->get('guzzle.twitter.client');
        $status = $twitterClient->get('statuses/user_timeline.json');
        $tweets_array=array();
        foreach($twitterAccounts as $twitterAccount)
        {
            $status->getQuery()->set('screen_name', $twitterAccount);
            $response = $status->send();
            
            $tweets = json_decode($response->getBody(),true);
            foreach($tweets as $tweets_one)
            {
                $index= strtotime($tweets_one['created_at']);
                $tweets_array[$index] = $tweets_one ;
                ksort($tweets_array,SORT_NUMERIC);
            }
        }
        
        foreach($tweets_array as $tweet)
        {
            $currentTime = time();
            $tweetTimestamp = strtotime($tweet['created_at']);
            if($currentTime-$tweetTimestamp<3600)
            {
                $tweetId="twitter::tweet::" . $tweet['id_str'];
                $tweet_pretty = $this->parse_message( $tweet );
               // Content create struct
                $createStruct = $contentService->newContentCreateStruct(
                    $repository->getContentTypeService()->loadContentTypeByIdentifier( 'tweets' ),
                    'ger-DE'
                );

                $createStruct->setField( 'text',$tweet_pretty, 'ger-DE' );
                $createStruct->setField( 'tweet', json_encode($tweet), 'ger-DE' );

                $createStruct->remoteId = $tweetId;
                
                try
                {
                    $contentDraft = $contentService->createContent(
                        $createStruct, array( $repository->getLocationService()->newLocationCreateStruct( $locationId ) )
                     );
                    $content = $contentService->publishVersion( $contentDraft->versionInfo );
                    $output->writeln( "Created Content 'tweet' with (Object)ID {$content->id}" );
                    sleep(2);
                }
                catch ( \Exception $e )
                {
                    $output->writeln( "An error occured creating the content: " . $e->getMessage() );
                    //$output->writeln( $e->getTraceAsString() );
                    continue;
                }
             }else{$output->writeln( "No new tweets being displayed...." );}
         }
    }
    
    function parse_message( &$tweet ) 
    {
        if ( !empty($tweet['entities']) ) 
        {
            $replace_index = array();
            $append = array();
            $text = $tweet['text'];
            foreach ($tweet['entities'] as $area => $items) 
            {
                $prefix = false;
                $display = false;
                switch ( $area ) {
                    case 'hashtags':
                        $find = 'text';
                        $prefix = '#';
                        $url = 'https://twitter.com/search/?src=hash&q=%23';
                        break;
                    case 'user_mentions':
                        $find = 'screen_name';
                        $prefix = '@';
                        $url = 'https://twitter.com/';
                        break;
                    /*case 'media':
                        $display = 'media_url_https';
                        $href = 'media_url_https';
                        $size = 'small';
                        break;*/
                    case 'urls':
                        $find = 'url';
                        $display = 'display_url';
                        $url = "expanded_url";
                        break;
                    default: break;
                }
                foreach ($items as $item) 
                {
                  /*if ( $area == 'media' ) 
                   *{
                        // $append[$item->$display] = "<img src=\"{$item->$href}:$size\" />";
                    }else{*/
                    $msg = $display ? $prefix.$item[$display] : $prefix.$item[$find];
                    $replace = $prefix.$item[$find];
                    $href = isset($item[$url]) ? $item[$url] : $url;
                    if (!(strpos($href, 'http') === 0)) $href = "http://".$href;
                    if ( $prefix ) $href .= $item[$find];
                    $with = "<a href=\"$href\">$msg</a>";
                    $replace_index[$replace] = $with;
                 //}
                }
            }
            foreach ($replace_index as $replace => $with) $tweet['text'] = str_replace($replace,$with,$tweet['text']);
            foreach ($append as $add) $tweet['text'] .= $add;
        
            return $tweet['text'];
        }
    }
}
