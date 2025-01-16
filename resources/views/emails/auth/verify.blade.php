<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate QR Code</title>
</head>
<body>
<h1>Generate QR Code</h1>

<!-- Form for submitting QR code data -->
<form action="{{ route('qr.generate') }}" method="POST">
    @csrf
    <div>
        <label for="accountNo">Account No:</label>
        <input type="text" name="accountNo" id="accountNo" required value="{{ old('accountNo') }}">
        @error('accountNo') <span>{{ $message }}</span> @enderror
    </div>

    <div>
        <label for="accountName">Account Name:</label>
        <input type="text" name="accountName" id="accountName" required value="{{ old('accountName') }}">
        @error('accountName') <span>{{ $message }}</span> @enderror
    </div>

    <div>
        <label for="acqId">Acquirer ID:</label>
        <input type="text" name="acqId" id="acqId" required value="{{ old('acqId') }}">
        @error('acqId') <span>{{ $message }}</span> @enderror
    </div>

    <div>
        <label for="amount">Amount:</label>
        <input type="text" name="amount" id="amount" required value="{{ old('amount') }}">
        @error('amount') <span>{{ $message }}</span> @enderror
    </div>

    <div>
        <label for="addInfo">Additional Info:</label>
        <input type="text" name="addInfo" id="addInfo" value="{{ old('addInfo') }}">
    </div>

    <div>
        <label for="format">Format:</label>
        <select name="format" id="format" required>
            <option value="text" {{ old('format') == 'text' ? 'selected' : '' }}>Text</option>
            <option value="html" {{ old('format') == 'html' ? 'selected' : '' }}>HTML</option>
        </select>
    </div>

    <div>
        <label for="template">Template:</label>
        <select name="template" id="template" required>
            <option value="compact" {{ old('template') == 'compact' ? 'selected' : '' }}>Compact</option>
            <option value="compact2" {{ old('template') == 'compact' ? 'selected' : '' }}>Compact2</option>
            <option value="detailed" {{ old('template') == 'detailed' ? 'selected' : '' }}>Detailed</option>
        </select>
    </div>

    <button type="submit">Generate QR Code</button>
</form>

@if(isset($qrDataURL))
    <h2>Your QR Code:</h2>
    <div>
        <!-- Display generated QR code as image -->
        <img src="{{$qrDataURL }}" alt="QR Code">
    </div>
@endif
</body>
</html>
