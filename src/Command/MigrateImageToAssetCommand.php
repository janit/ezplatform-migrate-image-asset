<?php

namespace Janit\MigrateEzImageToAssetBundle\Command;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SearchService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Repository\Values\Content\Content as ContentObject;
use eZ\Publish\Core\FieldType\Image\Value as ImageFieldValue;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\UserService;

class MigrateImageToAssetCommand extends Command
{
    protected static $defaultName = 'janit:migrate_image_to_asset';

    const IMAGE_CONTENT_TYPE = 'image';
    const IMAGE_LANGUAGE = 'eng-GB';
    const IMPORT_USER = 123;

    private $contentService;
    private $contentTypeService;
    private $locationService;
    private $searchService;
    protected $permissionResolver;
    protected $userService;

    public function __construct(
        string $name = null,
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        LocationService $locationService,
        SearchService $searchService,
        PermissionResolver $permissionResolver,
        UserService $userService
    )
    {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->locationService = $locationService;
        $this->searchService = $searchService;
        $this->permissionResolver = $permissionResolver;
        $this->userService = $userService;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Copies image field type contents to an image asset field')
            ->addArgument('type_identifier', InputArgument::REQUIRED, 'Identifier of content type whose to data is to be modified')
            ->addArgument('source_field', InputArgument::REQUIRED, 'Source field identifier')
            ->addArgument('target_field', InputArgument::REQUIRED, 'Target field identifier')
            ->addArgument('target_location', InputArgument::REQUIRED, 'Target location id where image objects should be created');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $contentTypeIdentifier = $input->getArgument('type_identifier');
        $sourceFieldIdentifier = $input->getArgument('source_field');
        $targetFieldIdentifier = $input->getArgument('target_field');
        $imageTargetLocationId = $input->getArgument('target_location');

        $this->permissionResolver->setCurrentUserReference(
            $this->userService->loadUser(SELF::IMPORT_USER)
        );

        $searchResults = $this->loadContentObjects($contentTypeIdentifier);

        foreach ($searchResults as $searchHit) {
            $contentObject = $searchHit->valueObject;
            $this->updateContentObject($contentObject, $sourceFieldIdentifier, $targetFieldIdentifier, $imageTargetLocationId);
            $io->writeln('Updated ' . $contentObject->contentInfo->name . ' (' . $contentObject->id . ')');
        }

        return 0;
    }

    private function loadContentObjects($contentTypeIdentifier): array
    {

        $query = new Query();
        $query->query = new Query\Criterion\ContentTypeIdentifier($contentTypeIdentifier);
        $query->limit = 1000;
        $result = $this->searchService->findContent($query);
        return $result->searchHits;

    }

    private function updateContentObject(ContentObject $contentObject, $sourceFieldIdentifier, $targetFieldIdentifier, $imageTargetLocationId): void
    {

        $imageObjectRemoteId = 'image-asset-' . $contentObject->id . '-' . $contentObject->getField($sourceFieldIdentifier)->fieldDefIdentifier;

        $imageFieldValue = $contentObject->getFieldValue($sourceFieldIdentifier);
        $imageObject = $this->createOrUpdateImage($imageObjectRemoteId, $imageTargetLocationId, $imageFieldValue);

        $contentDraft = $this->contentService->createContentDraft( $contentObject->contentInfo );

        $contentUpdateStruct = $this->contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = SELF::IMAGE_LANGUAGE;

        $contentUpdateStruct->setField($targetFieldIdentifier, $imageObject->id);

        $draft = $this->contentService->updateContent($contentDraft->versionInfo, $contentUpdateStruct);
        $content = $this->contentService->publishVersion($draft->versionInfo);

    }

    private function createOrUpdateImage(string $remoteId, int $parentLocationId, ImageFieldValue $imageFieldValue): ContentObject
    {

        $contentType = $this->contentTypeService->loadContentTypeByIdentifier(SELF::IMAGE_CONTENT_TYPE);

        $imageName = $imageFieldValue->fileName;
        $imagePath = getcwd() . '/public' . $imageFieldValue->uri;

        try {

            $contentObject = $this->contentService->loadContentByRemoteId($remoteId, [SELF::IMAGE_LANGUAGE]);

            $contentDraft = $this->contentService->createContentDraft( $contentObject->contentInfo );

            $contentUpdateStruct = $this->contentService->newContentUpdateStruct();
            $contentUpdateStruct->initialLanguageCode = SELF::IMAGE_LANGUAGE;

            $contentUpdateStruct->setField('name', $imageName);
            $contentUpdateStruct->setField('image', $imagePath);

            $draft = $this->contentService->updateContent($contentDraft->versionInfo, $contentUpdateStruct);
            $content = $this->contentService->publishVersion($draft->versionInfo);

        } catch (\eZ\Publish\Core\Base\Exceptions\NotFoundException $e){

            // Not found, create new object

            try {

                $contentCreateStruct = $this->contentService->newContentCreateStruct($contentType, SELF::IMAGE_LANGUAGE);
                $contentCreateStruct->remoteId = $remoteId;

                $contentCreateStruct->setField('name', $imageName);
                $contentCreateStruct->setField('image', $imagePath);

                $locationCreateStruct = $this->locationService->newLocationCreateStruct($parentLocationId);
                $draft = $this->contentService->createContent($contentCreateStruct, [$locationCreateStruct]);
                $content = $this->contentService->publishVersion($draft->versionInfo);

            } catch (\Exception $e){
                dump($e);
                die();
            }

        } catch (\Exception $e){
            dump($e);
            die();
        }

        return $content;

    }


}