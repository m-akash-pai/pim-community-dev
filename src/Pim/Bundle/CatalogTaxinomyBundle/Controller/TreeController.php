<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Controller;

use Pim\Bundle\CatalogTaxinomyBundle\Helper\JsonTreeHelper;

use Pim\Bundle\CatalogTaxinomyBundle\Entity\CategoryManager;

use Pim\Bundle\CatalogTaxinomyBundle\Entity\Tree;

use Doctrine\ORM\EntityManager;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
/**
 * Enter description here ...
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/tree")
 *
 */
class TreeController extends Controller
{
    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->get('doctrine.orm.entity_manager');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository()
    {
        return $this->getEntityManager()
                    ->getRepository('PimCatalogTaxinomyBundle:Tree');
    }

    /**
     * @return Response
     *
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        $res = $this->getRepository()->findAll();

        return $this->render(
            'PimCatalogTaxinomyBundle:Tree:index.html.twig',
            array('tree' => $repo->childrenHierarchy())
        );
    }

    /**
     * @return Response
     *
     * @Route("/tree")
     * @Template()
     */
    public function treeAction()
    {
        return $this->render('PimCatalogTaxinomyBundle:Tree:tree.html.twig');
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/children")
     * @Template()
     *
     * TODO : Must be XmlHttpRequest
     * TODO : Value must be posted ?!
     *
     * TODO : must return with 1 parameter
     * [{"attr":{"id":"node_2","rel":"drive"},"data":"C:","state":"closed"},{"attr":{"id":"node_6","rel":"drive"},"data":"D:","state":""}]
     */
    public function childrenAction(Request $request)
    {
        // initialize variables
        $parentId = $request->get('id');
        $recursive = false;

        // Get nodes from parent
        $categories = $this->getManager()->getChildren($parentId);

        // formate in json content
        $data = JsonTreeHelper::childrenResponse($categories);

        return $this->prepareJsonResponse($data);
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/search")
     * @Template()
     */
    public function searchAction(Request $request)
    {
        // get search data
        $search = $request->get('search');

        // TODO : Use request
//         SELECT left, right
//         FROM tree
//         WHERE title LIKE '%name%'
        $categories = $this->getRepository()->findAll();

        // formate in json content
        $json = '';
        foreach ($categories as $category) {
            $json .= '"#node_'. $category->getId() .'",';
        }
        $json = substr($json, 0, -1);
        $json = '['. $json .']';

        return $this->render('PimCatalogTaxinomyBundle:Tree:children.html.twig', array('json_categories' => $json));


//         $this->db->query("SELECT `".$this->fields["left"]."`, `".$this->fields["right"]."` FROM `".$this->table."` WHERE `".$this->fields["title"]."` LIKE '%".$this->db->escape($data["search_str"])."%'");
//         if($this->db->nf() === 0) return "[]";
//         $q = "SELECT DISTINCT `".$this->fields["id"]."` FROM `".$this->table."` WHERE 0 ";
//         while($this->db->nextr()) {
//             $q .= " OR (`".$this->fields["left"]."` < ".(int)$this->db->f(0)." AND `".$this->fields["right"]."` > ".(int)$this->db->f(1).") ";
//         }
//         $result = array();
//         $this->db->query($q);
//         while($this->db->nextr()) { $result[] = "#node_".$this->db->f(0); }
//         return json_encode($result);
    }

    /**
     * Create a new node
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Method("POST")
     * @Route("/createNode")
     * @Template()
     *
     * TODO : LEFT / RIGHT will be implemented with Category feature
     */
    public function createNodeAction(Request $request)
    {
        // create new object
        $category = $this->getManager()->createNewInstance();
        $category->setTitle($request->get('title'));
        $category->setType($request->get('type'));

        // find parent
        $parent = $this->getManager()->getCategory($request->get('id'));
        $category->setParent($parent);

        // persist object
        $this->getManager()->persist($category);

        // format response to json content
        $data = JsonTreeHelper::createNodeResponse(1, $category->getId());

        return $this->prepareJsonResponse($data);
    }

    /**
     * Rename a node
     * @param Request $request
     *
     * @return Response
     *
     * @Method("POST")
     * @Route("/renameNode")
     * @Template()
     */
    public function renameNodeAction(Request $request)
    {
        // update object
        $this->getManager()->rename($request->get('id'), $request->get('title'));

        // format response to json content
        $data = JsonTreeHelper::statusOKResponse();

        return $this->prepareJsonResponse($data);
    }

    /**
     * Return a response in json content type with well formated data
     * @param mixed $data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function prepareJsonResponse($data)
    {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Remove a node
     * @param Request $request
     *
     * @return Response
     *
     * @Method("POST")
     * @Route("/removeNode")
     * @Template()
     */
    public function removeAction(Request $request)
    {
        // find category and remove it
        $this->getManager()->removeFromId($request->get('id'));

        // format response to json content
        $data = JsonTreeHelper::statusOKResponse();

        return $this->prepareJsonResponse($data);
    }

    /**
     * Move a node
     * @param Request $request
     *
     * @return Response
     *
     * @Method("POST")
     * @Route("/moveNode")
     * @Template()
     *
     * TODO : Manage cut/paste and copy/paste (clone then paste)
     */
    public function moveNodeAction(Request $request)
    {

    }

    /**
     * Analyse tree
     * @param Request $request
     *
     * @return Response
     *
     * @Method("POST")
     * @Route("/analyse")
     * @Template()
     */
    public function analyseAction(Request $request)
    {

    }

    /**
     * Reconstruct tree
     * @param Request $request
     *
     * @return Response
     *
     * @Method("POST")
     * @Route("/reconstruct")
     * @Template()
     */
    public function reconstructAction(Request $request)
    {

    }

    /**
     * Get a category entity from an id
     * @param integer $categoryId
     *
     * @return boolean
     */
    protected function getCategory($categoryId)
    {
        return $this->getRepository()->findOneBy(array('id' => $categoryId));
    }

    /**
     * @return CategoryManager
     */
    protected function getManager()
    {
        return $this->get('pim.catalog_taxinomy.category_manager');
    }
}