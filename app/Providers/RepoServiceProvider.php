<?php

namespace App\Providers;

use App\Interfaces\Repository\{AuthRepositoryInterface, ChatRoomRepositoryInterface, FileRepositoryInterface, MessageRepositoryInterface, NotificationRepositoryInterface, ProfileRepositoryInterface, ProjectRepositoryInterface, ProposalRepositoryInterface, SearchRepositoryInterface, SkillRepositoryInterface, TransactionRepositoryInterface, VerificationRepositoryInterface};
use App\Repositories\{AuthRepository, ChatRoomRepository, FileRepository, MessageRepository, NotificationRepository, ProfileRepository, ProjectRepository, ProposalRepository, SearchRepository, SkillRepository, TransactionRepository, VerificationRepository};
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class RepoServiceProvider extends ServiceProvider implements DeferrableProvider
{
	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
		$this->app->bind(ProfileRepositoryInterface::class, ProfileRepository::class);
		$this->app->bind(SkillRepositoryInterface::class, SkillRepository::class);
		$this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);
		$this->app->bind(ProposalRepositoryInterface::class, ProposalRepository::class);
		$this->app->bind(FileRepositoryInterface::class, FileRepository::class);
		$this->app->bind(MessageRepositoryInterface::class, MessageRepository::class);
		$this->app->bind(NotificationRepositoryInterface::class, NotificationRepository::class);
		$this->app->bind(ChatRoomRepositoryInterface::class, ChatRoomRepository::class);
		$this->app->bind(SearchRepositoryInterface::class, SearchRepository::class);
		$this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
		$this->app->bind(VerificationRepositoryInterface::class, VerificationRepository::class);
	}


	public function provides():array
	{
		return [
			AuthRepositoryInterface::class,
			ProfileRepositoryInterface::class,
			SkillRepositoryInterface::class,
			ProjectRepositoryInterface::class,
			ProposalRepositoryInterface::class,
			FileRepositoryInterface::class,
			MessageRepositoryInterface::class,
			NotificationRepositoryInterface::class,
			ChatRoomRepositoryInterface::class,
			SearchRepositoryInterface::class,
			TransactionRepositoryInterface::class,
			VerificationRepositoryInterface::class,
		];
	}


	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot()
	{
	}
}
