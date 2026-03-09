<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
// use DB;
use Illuminate\Support\Facades\DB;
use Zendesk;
// use Redirect;
// use Session;
class ShopifyController extends Controller
{
    public function requestuploadcsv(Request $request)
    {

        $csvFile = $request->file('csv_file');

        // Ensure the file is uploaded
        if ($csvFile && $csvFile->isValid()) {
        dd('asdsadsadsad    ');

            // Define the directory where you want to save the file
            $directory = 'bulk-upload-csv';

            // Define the filename (optional: keep original name or generate custom)
            $fileName = $csvFile->getClientOriginalName(); // Use original name
            // Or generate a unique name: $fileName = uniqid() . '.' . $csvFile->getClientOriginalExtension();

            $fileName = uniqid() . '.' . $fileName;
            // Save the file to the specified directory
            $path = $csvFile->storeAs($directory, $fileName, 'public');
                $csvFilePath = storage_path("app/public/bulk-upload-csv/{$fileName}"); // Adjust path as needed
              $fileData = file_get_contents($csvFilePath);

              try{
                  $ch = curl_init();
                  curl_setopt($ch, CURLOPT_URL, "https://mallshark.zendesk.com/api/v2/uploads.json?filename=" . urlencode($fileName));
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                  curl_setopt($ch, CURLOPT_POST, true);
                  curl_setopt($ch, CURLOPT_HTTPHEADER, [
                      "Authorization: Basic " . base64_encode("fugatesklcc@yahoo.com/token:SB6vDav5v0RDwFJ7nNrPZRmGBBsPkW3eBgHEW2N0"),
                      "Content-Type: application/binary"
                  ]);
                  curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($csvFilePath));

                  $uploadResponse = curl_exec($ch);
                  curl_close($ch);

                  $uploadResponse = json_decode($uploadResponse, true);
                  $uploadToken = $uploadResponse['upload']['token'];
                  $newTicket = Zendesk::tickets()->create([
                  'subject' => 'New testing ticket',
                  'comment' => [
                      'body' => 'Please find the attached CSV file.',
                      'uploads' => [$uploadToken] // Attach file using upload token
                  ],
                  'priority' => 'normal',
                  'requester_id' =>'10860489149201',
                  'type' => 'problem'
              ]);
                  dd($newTicket , "asdasdsadsadsadasd");
              }
                  catch (\Exception $e) {

}
  // Return the storage path or success message
  return response()->json([
      'message' => 'File uploaded successfully',
      'path' => \Storage::url($path),
  ]);
}
    }
}
