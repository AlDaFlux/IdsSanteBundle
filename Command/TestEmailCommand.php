<?php

namespace Aldaflux\AldafluxIdsSanteBundle\Command;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test:email',
    description: "Test l'envois d'email",
)]
class TestEmailCommand extends Command
{

    public function __construct(protected  MailerInterface $mailer)
    {
        parent::__construct();
    }
    
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::OPTIONAL, 'EMail to sent');

    }

   
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * This method is executed after initialize(). It usually contains the logic
     * to execute to complete this command task.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        if ($input->getArgument('email'))
        {
            $email=$input->getArgument('email');
        } 
        else
        {
        return Command::FAILURE;
        }
        $this->aliseaMailer->mailToEmail($email, "TOTP", "totp.html.twig", ['otp'=>666666]);

        
        $argsDefault=['title'=>$titre, 'css_email'=>$this->cssEmail];
        $args= array_merge($args,$argsDefault);
        $email = (new TemplatedEmail())
            ->to($emailAdresse)
            ->subject("ALISEA - ".$titre)
            ->htmlTemplate('email/' . $template)
            ->context($args);
        
        return($this->mailer->send($email));
        
        
        $this->io->success("Le mail a été envoyé à ".$email);
        return Command::SUCCESS;
    }
}
