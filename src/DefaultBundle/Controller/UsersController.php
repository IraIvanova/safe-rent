<?php
namespace DefaultBundle\Controller;

use DefaultBundle\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use DefaultBundle\Form\UsersType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Users controller.
 *
 */
class UsersController extends Controller
{
    public function loginAction()
    {
        $this->redirectToRoute("report_new");
        return $this->render('users/login.html.twig');
    }
    /**
     * Lists all user entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('DefaultBundle:Users')->findAll();
        return $this->render('users/index.html.twig', array(
            'users' => $users,
        ));
    }
    /**
     * Creates a new user entity.
     *
     */
    /*public function newAction(Request $request)
    {
        $user = new Users();
        $form = $this->createForm('DefaultBundle\Form\UsersType', $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('users_show', array('id' => $user->getId()));
        }
        return $this->render('users/new.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }*/
    public function newAction(Request $request)
    {
        $user = new Users();
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);
        if ($request->isMethod("POST"))
        {
            $passwordHashed = $this->get('security.password_encoder')->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($passwordHashed);
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user);
            $manager->flush();
            $cid = $user->getId();
            $email = $user->getEmail();
            $name = $user->getName();
            $sol = $email."/".$name."/".$cid;
            $str = base64_encode($sol);
            $str = "http://" . "$_SERVER[HTTP_HOST]" . "/users/email/confirm/" . $str;
            $mail = $this->get("myshop_admin.sending_mail");
            $mail->sendEmail("Перейдите по ссылке, что бы авторезироваться" . " " . "-" . " " . "<a href='$str'>$str</a>", $email);
            /* $this->addFlash("success", "Спасибо за регистрацию!");*/
            return $this->redirectToRoute("users_go_to_email");
        }
        return $this->render('users/new.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }
    public function confirmUserAction($str)
    {
        $str = base64_decode($str);
        $str = explode('/', $str);
        $id = array_pop($str);
        (integer)$id;
        $manager = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()->getRepository("DefaultBundle:Users")->find($id);
        $user->setIsActive(true);
        $manager->persist($user);
        $manager->flush();
        return $this->render('users/confirmUser.html.twig');
    }
    public function goToEmailAction()
    {
        return $this->render('users/goToEmail.html.twig');
    }
    /**
     * Finds and displays a user entity.
     *
     */
    public function showAction(Users $user)
    {
        $deleteForm = $this->createDeleteForm($user);
        return $this->render('users/show.html.twig', array(
            'user' => $user,
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Displays a form to edit an existing user entity.
     *
     */
    public function editAction(Request $request, Users $user)
    {
        $deleteForm = $this->createDeleteForm($user);
        $editForm = $this->createForm('DefaultBundle\Form\UsersType', $user);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('users_edit', array('id' => $user->getId()));
        }
        return $this->render('users/edit.html.twig', array(
            'user' => $user,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a user entity.
     *
     */
    public function deleteAction(Request $request, Users $user)
    {
        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }
        return $this->redirectToRoute('users_index');
    }
    /**
     * Creates a form to delete a user entity.
     *
     * @param Users $user The user entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Users $user)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('users_delete', array('id' => $user->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}