<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Repository;

use App\Domain\Hr\MemoryPages\Entity\MemoryPage;
use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Portal\Files\Entity\File;
use Database\Connection\ParamType;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<File>
 */
class MemoryPagePhotoQueryRepository extends QueryRepository
{
    protected string $entityName = File::class;

    /** @return Enumerable<int, File>  */
    public function getAllForMemoryPage(MemoryPage $memoryPage): Enumerable
    {
        $sql = "
            select 
                id,
                name,   
                fpath,
                idemp,
                parentid,
                parent_tbl,
                ext,                
                date_edit,
                is_on_static                
            from test.cp_files cf
            where
            (cf.parentid = :personalPageId  AND cf.parent_tbl = :memory_page_main_image_collection)
            OR 
            (cf.parentid = :personalPageId AND cf.parent_tbl = :memory_page_other_image_collection)
            OR 
            (cf.parentid in (:commentIdList) AND cf.parent_tbl = :memory_page_comment_image_collection)
            OR 
            (cf.parentid in (:authorCommentId) AND cf.parent_tbl = :users_photo_collection)
        ";

        $commentIds = [];
        $commentatorIds = [];
        foreach ($memoryPage->getComments() as $comment) {
            $commentIds[] = $comment->getId();
            $commentatorIds[] = $comment->getEmployee()->id;
        }

        return $this->query($sql,
            [
                'personalPageId'                       => $memoryPage->getId(),
                'memory_page_main_image_collection'    => MemoryPagePhotoService::MAIN_IMAGE_COLLECTION,
                'memory_page_other_image_collection'   => MemoryPagePhotoService::OTHER_IMAGE_COLLECTION,
                'memory_page_comment_image_collection' => MemoryPagePhotoService::COMMENTS_IMAGE_COLLECTION,
                'users_photo_collection'               => MemoryPagePhotoService::USER_AVATAR_COLLECTION,
                'commentIdList'                        => array_unique($commentIds),
                'authorCommentId'                      => array_unique($commentatorIds),
            ],
            [
                'commentIdList'   => ParamType::ARRAY_INTEGER,
                'authorCommentId' => ParamType::ARRAY_INTEGER,
            ]
        );
    }
}
