function displayPlayControls(episodeUrl) {
    // If we already revealed the play controls don't do it again
    if (this.getAttribute('disabled') === 'disabled') {
        return;
    }

    // Find the area to
    var $playDiv = $(this).parent().siblings('.play');
    if (!$playDiv.length) {
        alert('Failed to create play controls!');
        this.setAttribute('disabled', 'disabled');
        return;
    }

    var audio = document.createElement('audio');
    audio.innerHTML = 'Your browser does not support the audio element.';
    audio.controls = 'controls';
    audio.src = episodeUrl;
    audio.type = 'audio/mpeg';
    $playDiv.append(audio);

    // Disable this so we don't do it multiple times
    this.setAttribute('disabled', 'disabled');

    // Start playing it
    audio.play();
}
