# mPDF View

Extension for Kohana's View class that renders as a PDF instead of HTML. Uses [MPDF](http://www.mpdf1.com/mpdf/) to render normal HTML views as PDF Files.

## Installation

If your application is a Git repository:

    git submodule add git://github.com/smgladkovskiy/kohana-mpdf.git modules/mpdf
    git submodule update --init --recursive

Or clone the the module separately:

    cd modules
    git clone git://github.com/smgladkovskiy/kohana-mpdf.git mpdf

### Update mPDF

    cd modules/mpdf
    git submodule update --init

### Configuration

Edit `application/bootstrap.php` and add a the module:

    Kohana::modules(array(
        ...
        'mpdf' => 'modules/mpdf',
        ...
    ));

## Usage

Placed in a controller action:

    // Load a view using the PDF extension
    $pdf = View_MPDF::factory('pdf/example');
    
    // Use the PDF as the request response
    $this->response->body($pdf);