<?php

namespace Aldaflux\AldafluxIdsSanteBundle\Command;



use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;



class TestUserCommand extends Command
{
    protected static $defaultName = 'ids:user:test';

    private $io;
    private $passwordHasher;
    private $em;
    private $parameter;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameter, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->em=$em;
        $this->passwordHasher = $passwordHasher;
        $this->parameter= $parameter;
        
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('test si un utilisateur existe')
            ->setHelp($this->getCommandHelp())
            ->addArgument('username', InputArgument::OPTIONAL, 'The username of the new user')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (null !== $input->getArgument('username')) {
            return;
        }
            $username = $input->getArgument('username');
            if (null !== $username) {
                $this->io->text(' > <info>Username</info>: '.$username);
            } else {
                $username = $this->io->ask('Username', null);
                $input->setArgument('username', $username);
            }
   
    }

    /**
     * This method is executed after interact() and initialize(). It usually
     * contains the logic to execute to complete this command task.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $username = $input->getArgument('username');

        $classeUser=$this->parameter->get("aldaflux_ids_sante.user.class");
        $method=$this->parameter->get("aldaflux_ids_sante.user.find_by");

        $user=$this->em->getRepository($classeUser)->{$method}($username);
        
        if ($user)
        {

            $output->writeln("Utilisateur ".$username." trouvé ! ");

        }
        else
        {
            $output->writeln("<error>Utilisateur non trouvé ! </error>");
            $output->writeln("classeUser :".$classeUser);
            $output->writeln("method :".$method);
            $output->writeln("username:".$username);
        }

        
              
        //->findOneByUsername($form->getData()->GetUserName())


        return Command::SUCCESS;
    }


    /**
     * The command help is usually included in the configure() method, but when
     * it's too long, it's better to define a separate method to maintain the
     * code readability.
     */
    private function getCommandHelp(): string
    {
        return <<<'HELP'
The <info>%command.name%</info> command creates new users and saves them in the database:
  <info>php %command.full_name%</info> <comment>username password email</comment>
By default the command creates regular users. To create administrator users,
add the <comment>--admin</comment> option:
  <info>php %command.full_name%</info> username password email <comment>--admin</comment>
If you omit any of the three required arguments, the command will ask you to
provide the missing values:
  # command will ask you for the email
  <info>php %command.full_name%</info> <comment>username password</comment>
  # command will ask you for the email and password
  <info>php %command.full_name%</info> <comment>username</comment>
  # command will ask you for all arguments
  <info>php %command.full_name%</info>
HELP;
    }
}