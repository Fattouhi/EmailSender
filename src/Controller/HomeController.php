<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'message' => 'Bienvenue sur Symfony 6 !',
        ]);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $transport = Transport::fromDsn($_ENV['MAILER_DSN']);
        $mailer = new Mailer($transport);
        $form = $this->createForm(ContactType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get form data
            $data = $form->getData();

            // Create the email
            $email = (new Email())
                ->from('jasserfattouhi8@gmail.com') // Sender's email
                ->to($data['email']) // Recipient's email from the form
                ->subject('New Contact Form Submission')
                ->text(sprintf(
                    "Name: %s\nEmail: %s\nMessage: %s",
                    $data['name'],
                    $data['email'],
                    $data['message']
                ));

            try {
                // Send the email
                $mailer->send($email);

                // Add a flash message to confirm the email was sent
                $this->addFlash('success', 'Votre message a été envoyé avec succès !');
            } catch (\Exception $e) {
                // Log the error
                $this->addFlash('error', 'Une erreur s\'est produite lors de l\'envoi de l\'email.');
            }

            // Redirect to the contact page
            return $this->redirectToRoute('contact');
        }

        return $this->render('home/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
