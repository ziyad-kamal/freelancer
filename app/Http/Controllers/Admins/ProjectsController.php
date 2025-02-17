<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Interfaces\Repository\Admins\ProjectRepositoryInterface;
use App\Interfaces\Repository\FileRepositoryInterface;
use App\Interfaces\Repository\SkillRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectsController extends Controller
{
	public function __construct(private ProjectRepositoryInterface $projectRepository) 
	{
		$this->middleware('auth:admins');
	}
    // MARK: index   
	public function index():View
	{
		$projects = $this->projectRepository->indexProject();

		return view('admins.project.index',compact('projects'));
	}

    // MARK: create   
	public function create():View
	{
		return view('admins.project.create');
	}

    // MARK: store   
    public function store(ProjectRequest $request, FileRepositoryInterface $fileRepository, SkillRepositoryInterface $skillRepository):RedirectResponse
	{
		$this->projectRepository->storeProject($request,$fileRepository,$skillRepository);

		return to_route('admin.project.create')->with('success','project is created successfully');
	}

    // MARK: show   
	public function show(string $slug):View
	{
		$data = $this->projectRepository->showProject($slug);

		return view('admins.project.show', $data);
	}

    // MARK: edit   
	public function edit(int $id):View
	{
		return view('');
	}

    // MARK: update   
	public function update(ProjectRequest $request , int $id):RedirectResponse
	{
		return to_route('');
	}

    // MARK: destroy   
	public function destroy(int $id):RedirectResponse
	{
		return to_route('');
	}
}
