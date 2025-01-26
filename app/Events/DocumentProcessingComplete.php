<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentProcessingComplete implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $documentId;
    public $status;

    public function __construct($documentId, $status)
    {
        $this->documentId = $documentId;
        $this->status = $status;
    }

    public function broadcastOn()
    {
        return new Channel('document-processing');
    }
}
