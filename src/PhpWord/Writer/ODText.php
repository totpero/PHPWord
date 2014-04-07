<?php
/**
 * PHPWord
 *
 * @link        https://github.com/PHPOffice/PHPWord
 * @copyright   2014 PHPWord
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 */

namespace PhpOffice\PhpWord\Writer;

use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Writer\ODText\Content;
use PhpOffice\PhpWord\Writer\ODText\Manifest;
use PhpOffice\PhpWord\Writer\ODText\Meta;
use PhpOffice\PhpWord\Writer\ODText\Mimetype;
use PhpOffice\PhpWord\Writer\ODText\Styles;

/**
 * ODText writer
 */
class ODText extends AbstractWriter implements WriterInterface
{
    /**
     * Create new ODText writer
     *
     * @param PhpWord $phpWord
     */
    public function __construct(PhpWord $phpWord = null)
    {
        // Assign PhpWord
        $this->setPhpWord($phpWord);

        // Set writer parts
        $this->writerParts['content'] = new Content();
        $this->writerParts['manifest'] = new Manifest();
        $this->writerParts['meta'] = new Meta();
        $this->writerParts['mimetype'] = new Mimetype();
        $this->writerParts['styles'] = new Styles();
        foreach ($this->writerParts as $writer) {
            $writer->setParentWriter($this);
        }
    }

    /**
     * Save PhpWord to file
     *
     * @param  string $filename
     * @throws Exception
     */
    public function save($filename = null)
    {
        if (!is_null($this->phpWord)) {
            $filename = $this->getTempFile($filename);
            $objZip = $this->getZipArchive($filename);

            // Add mimetype to ZIP file
            //@todo Not in \ZipArchive::CM_STORE mode
            $objZip->addFromString('mimetype', $this->getWriterPart('mimetype')->writeMimetype($this->phpWord));

            // Add content.xml to ZIP file
            $objZip->addFromString('content.xml', $this->getWriterPart('content')->writeContent($this->phpWord));

            // Add meta.xml to ZIP file
            $objZip->addFromString('meta.xml', $this->getWriterPart('meta')->writeMeta($this->phpWord));

            // Add styles.xml to ZIP file
            $objZip->addFromString('styles.xml', $this->getWriterPart('styles')->writeStyles($this->phpWord));

            // Add META-INF/manifest.xml
            $objZip->addFromString('META-INF/manifest.xml', $this->getWriterPart('manifest')->writeManifest($this->phpWord));

            // Close file
            if ($objZip->close() === false) {
                throw new Exception("Could not close zip file $filename.");
            }

            $this->cleanupTempFile();
        } else {
            throw new Exception("PhpWord object unassigned.");
        }
    }
}
