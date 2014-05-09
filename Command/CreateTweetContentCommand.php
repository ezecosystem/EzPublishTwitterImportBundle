<?php

namespace Xrow\TwitterImportBundle\Command;

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
            $repository->getUserService()->loadUserByLogin( 'admin' )
        );

        $contentService = $repository->getContentService();
        
        //Getting a twitter user's latest posts using Guzzle
        $twitterClient = $this->getApplication()->getKernel()->getContainer()->get('guzzle.twitter.client');
        $status = $twitterClient->get('statuses/user_timeline.json');
        
        foreach($twitterAccounts as $twitterAccount)
        {
            $status->getQuery()->set('screen_name', $twitterAccount);
            $response = $status->send();
        
            $tweets = json_decode($response->getBody());
            $currentTime = time();
            foreach($tweets as $tweet){
              $tweetTimestamp = strtotime($tweet->created_at);
              if($currentTime-$tweetTimestamp<86400){

                $tweetId="twitter::tweet::" . $tweet->id_str;
               // $output->writeln( "Tweets:{$tweet->text}" );
               // $output->writeln( "User:{$tweet->user->name}" );
               // $output->writeln( "TweetTimestamp:{$tweetTimestamp}" );
               // $tweet_pretty=$this->linkEntitiesWithinText($tweet);
               
               // Content create struct
                $createStruct = $contentService->newContentCreateStruct(
                    $repository->getContentTypeService()->loadContentTypeByIdentifier( 'tweets' ),
                    'eng-GB'
                );

                $createStruct->setField( 'name',$tweet->text , 'eng-GB' );
                $createStruct->setField( 'tweet', json_encode($tweet), 'eng-GB' );

                $createStruct->remoteId = $tweetId;
                    
                try
                {
                    $contentDraft = $contentService->createContent(
                        $createStruct, array( $repository->getLocationService()->newLocationCreateStruct( $locationId ) )
                     );
                    $content = $contentService->publishVersion( $contentDraft->versionInfo );
                    $output->writeln( "Created Content 'tweet' with (Object)ID {$content->id}" );
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
    }
    
    function linkEntitiesWithinText($apiResponseTweetObject) 
    {
        $characters = str_split($apiResponseTweetObject->text);
    
        //for @user_mentions
        foreach ($apiResponseTweetObject->entities->user_mentions as $entity) {
            $link = "https://twitter.com/" . $entity->screen_name;
            $characters[$entity->indices[0]] = "<a href=\"$link\">" . $characters[$entity->indices[0]];
            $characters[$entity->indices[1] - 1] .= "</a>";
        }
    
        //for #hashtags
        foreach ($apiResponseTweetObject->entities->hashtags as $entity) {
            $link = "https://twitter.com/search?q=%23" . $entity->text;
            $characters[$entity->indices[0]] = "<a href=\"$link\">" . $characters[$entity->indices[0]];
            $characters[$entity->indices[1] - 1] .= "</a>";
        }
    
        //for urls
        foreach ($apiResponseTweetObject->entities->urls as $entity) {
            $link = $entity->expanded_url;
            $characters[$entity->indices[0]] = "<a href=\"$link\">" . $characters[$entity->indices[0]];
            $characters[$entity->indices[1] - 1] .= "</a>";
        }
    
        //for media
        /*foreach ($apiResponseTweetObject->entities->media as $entity) {
            $link = $entity->expanded_url;
            $characters[$entity->indices[0]] = "<a href=\"$link\">" . $characters[$entity->indices[0]];
            $characters[$entity->indices[1] - 1] .= "</a>";
        }*/

        return implode('', $characters);
    }
}
