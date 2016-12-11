<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;
use AppBundle\Entity\Files;


/**
 * Files
 *
 * @ORM\Table(name="files")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FilesRepository")
 */
class Files
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @Assert\File(
     *      maxSize="6000000",
     *      mimeTypes = {"application/xml", "application/csv","text/plain"}
     * )
     */
    private $file;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $path;
    
    private $filename;
    
     /**
     * One Product has One Shipping.
     * @ORM\OneToOne(targetEntity="Files")
     * @ORM\JoinColumn(name="file2_id", referencedColumnName="id", nullable=true)
     */
    private $file2;
   
    
    /**
     * @ORM\Column(type="string", length=30,nullable=true)
     */
    public $ext = null;
    /**
     * 
     * @return type
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * 
     * @param type $path
     * 
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
                
    }
    
    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return 'uploads';
    }
    
    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     * 
     * @return type
     */
    public function upload()
{
        
    // zmienna file może być pusta jeśli pole nie jest wymagane
    if (null === $this->file) {
        return;
    }
    
    if($this->ext === null)
    {
        $this->ext = $this->file->guessExtension();
    }
    // używamy oryginalnej nazwy pliku ale nie powinieneś tego robić
    // aby zabezpieczyć się przed ewentualnymi problemami w bezpieczeństwie

    // metoda move jako atrybuty przyjmuje ścieżkę docelową gdzie trafi przenoszony plik
    // oraz ścieżkę z której ma przenieś plik
    $this->file->move($this->getUploadRootDir(), $this->filename . '.' . $this->ext);


    
    // wyczyść zmienną file ponieważ już jej nie potrzebujemy
    $this->file = null;
}

    /**
     * @ORM\PrePersist()
     */
    public function preUpload($extension = null)
    {        if  (null !== $this->file){
            // zrób cokolwiek chcesz aby wygenerować unikalną nazwę
            // ustaw zmienną patch ścieżką do zapisanego pliku
            
            if($this->ext === null)
            {
                $this->ext = $this->file->guessExtension();
            }
            
            $this->filename = sha1(uniqid(mt_rand(), true));
            $this->setPath($this->filename . '.' . $this->ext);
        }
       
    }
    
    /**
     * @ORM\PreUpdate()
     */
    public function preUpdateUpload()
    {
        if($this->ext === null)
        {
            $this->ext = $this->file->guessExtension();
        }
        
         if($this->file !== null){
            $this->removeUpload();
            $this->filename = sha1(uniqid(mt_rand(), true));
            $this->setPath($this->filename . '.' . $this->ext);
        }
    }
    
    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function getFile()
    {
        return $this->file;
    }
    
    public function setFile($file)
    {
       $this->file = $file;
       return $this;
    }
    
   
    
    public function setExt($ext)
    {
        $this->ext = $ext;
        return $this;
    }
    
    public function getExt()
    {
        return $this->ext;
    }
    
    
    public function setFile2(Files $file2)
    {
        $this->file2 = $file2;
        return $this;
    }
    
    public function getFile2()
    {
        return $this->file2;
    }
}
