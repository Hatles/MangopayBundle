<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 06/07/2017
 * Time: 13:39
 */

namespace Troopers\MangopayBundle\Entity;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

class KycPage implements KycPageInterface
{
    /**
     * @var File
     * @Assert\NotBlank(message="Please, upload a document.")
     * @Assert\File(
     *     maxSize = "6M",
     *     mimeTypes = {"application/pdf", "application/x-pdf", "image/jpeg", "image/gif", "image/png"},
     *     mimeTypesMessage = "Please upload a valid document (.pdf, .jpeg, .jpg, .gif and .png)"
     * )
     */
    private $file;

    /**
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param File $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return string
     *      The base64 encoded file which needs to be uploaded
     */
    public function getFileBase64()
    {
        $data = file_get_contents($this->file->getPathname());
        return base64_encode($data);
    }
}