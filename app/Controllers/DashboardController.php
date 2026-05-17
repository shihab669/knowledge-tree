<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Models\Node;

class DashboardController
{
    private Node $node;

    public function __construct()
    {
        $this->node = new Node();
    }

    public function index(): void
    {
        Auth::requireAuth();

        $userId = Auth::getUserId();
        $tree = $this->node->getTree($userId);
        $recentNodes = $this->node->getRecent($userId, 5);
        $totalNodes = $this->node->getCount($userId);

        Response::view('dashboard', [
            'tree' => $tree,
            'recentNodes' => $recentNodes,
            'totalNodes' => $totalNodes,
            'username' => Auth::getUsername()
        ]);
    }

    public function settings(): void
    {
        Auth::requireAuth();
        Response::view('settings', [
            'username' => Auth::getUsername()
        ]);
    }
}
