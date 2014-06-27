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
        if($this->getContainer()->get('import.user')->getUser() =="")
        {
            $import_user="admin";
        }else{
            $import_user = $this->getContainer()->get('import.user')->getUser();
        }
        $repository->setCurrentUser(
            $repository->getUserService()->loadUserByLogin( $import_user )
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
               // Content create struct
                $createStruct = $contentService->newContentCreateStruct(
                    $repository->getContentTypeService()->loadContentTypeByIdentifier( 'tweets' ),
                    'ger-DE'
                );

                $createStruct->setField( 'text',$tweet['text'] , 'ger-DE' );
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
}