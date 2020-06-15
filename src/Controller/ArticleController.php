<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comments;
use App\Entity\Tags;
use App\Form\AddCommentFormType;
use App\Form\AddArticleFormType;
use App\Repository\ArticleRepository;
use App\Repository\CommentsRepository;
use App\Repository\TagsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    /**
     * @Route("/article/add", name="add_article")
     */
    public function add(Request $request)
    {
        $article = new Article();
        $form = $this->createForm(AddArticleFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $article=$form->getData();


                /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['imageFileName']->getData();

            if ($uploadedFile) {
                $destination = $this->getParameter('kernel.project_dir') . '/public/uploads/article_image';
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
                $uploadedFile->move(
                    $destination,
                    $newFilename
                );
                $article->setFilePathName($newFilename);
            }
            $specificTags=$request->request->get("add_article_form");
            $article->setAuthor($this->getUser());
                if (isset($specificTags["specificTagName"])) {
                    foreach ($specificTags["specificTagName"] as $tag) {
                        $tag = $this->getDoctrine()->getRepository(Tags::class)->find($tag);
                        $article->addTag($tag);
                    }
                }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('main_page');
        }

        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
            'addArticleForm'=>$form->createView()
        ]);
    }

    /**
     * @Route("/article/list", name="article_list")
     */
    public function ArticleList(ArticleRepository $articleRepository)
    {
        $articles=$articleRepository->findAll();

        return $this->render('article/articleList.html.twig', [
            'controller_name' => 'ArticleController',
            'articles'=>$articles]);
    }

    /**
     * @Route("/article/details/{id}", name="article_details")
     */
    public function ArtilceDetails(Request $request,ArticleRepository $articleRepository,$id,CommentsRepository $commentsRepository)
    {
        $article=$articleRepository->find($id);
        $allComments=$commentsRepository->findBy(['article'=>$article]);
        $comment = new Comments();
        $form = $this->createForm(AddCommentFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $comment->setAuthor($this->getUser());
            $comment->setArticle($article);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
        }

        return $this->render('article/articleDetails.html.twig', [
            'comments'=> $allComments,
            'controller_name' => 'ArticleController',
            'addCommentForm'=>$form->createView(),
            'article'=>$article]);
    }

    /**
     * @Route("/article/edit/{article}", name="edit_article")
     */
    public function edit(Request $request,Article $article, TagsRepository $tagsRepository)
    {
        $form = $this->createForm(AddArticleFormType::class, $article);
        $articleTags=$article->getTags()->getValues();

        $form->get('specificTagName')->setData($article->getTags()->getValues());
        $form->handleRequest($request);

        $hasTag=false;
        if (!empty($articleTags)) {
            $hasTag=true;}

        $specificTags=$request->request->get("add_article_form");
        $newTags=new ArrayCollection();

        if ($form->isSubmitted() && $form->isValid()) {
            foreach($specificTags["specificTagName"] as $tag)
            {
                $newTags->add($tagsRepository->find($tag));
            }
            foreach($newTags as $nt )   {
                if (false===in_array($nt,$articleTags)){
                    $article->addTag($nt);

                }

            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();
        }

        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
            'addArticleForm'=>$form->createView(),
            'hasTag'=>$hasTag
        ]);
    }

    /**
     * @Route("/article/delete/{article}", name="article_delete")
     */
    public function DeleteArticle(Article $article)
    {

        if($this->isGranted('OWNER',$article)){
            $entityManager = $this->getDoctrine()->getManager();

            foreach ($article->getTags() as $tag){
                $article->removeTag($tag);
            }
            foreach($article->getComments() as $comment){
                $article->removeComment($comment);
            }
            $entityManager->remove($article);
            $entityManager->flush();
            return $this->redirect($_SERVER['HTTP_REFERER']);

        }

        return $this->redirect($_SERVER['HTTP_REFERER']);

    }

    /**
     * @Route("/article/details/{id}/like", name="article_like", methods={"POST"})
     */
    public function LikeArticle(Article $article, EntityManagerInterface $em)
    {
        $article->incrementLikes();
        $em->flush();
        return new JsonResponse(['likes' => $article->getLikes()]);
    }

    /**
     * @Route("/article/tag-select", name="article_tag_select")
     */
    public function ArticleSpecyficTags (Request $request,EntityManagerInterface $em){
        $article = new Article();
        $tag=$em->getRepository(Tags::class)->find($request->query->get('tags'));

        $article->addTag($tag);
        $form = $this->createForm(AddArticleFormType::class, $article);
        if (!$form->has('specificTagName')) {
            return new Response('null', 204);
        }
        return $this->render('article/specificTag.html.twig', [
            'articleForm' => $form->createView(),
        ]);

    }
}
