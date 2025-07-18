// Inisialisasi variabel
let play_state = 0; // 0: paused, 1: playing, 2: ended
let vol_state = 1; // 0: muted, 1: unmuted
let last_vol = 1; // Volume sebelumnya
let a; // Variabel timeout untuk mousemove

// Inisialisasi elemen volume
var width = document.querySelector(".volume-button").getBoundingClientRect().width;
document.querySelector(".present-volume").style.width = `${(width - 18) * last_vol}px`;

// Fungsi saat dokumen dimuat
window.onload = function () {
    let m = document.querySelector(".my-video")
    var minutes = Math.floor(m.duration / 60);
    minutes = (minutes > 9) ? minutes : `0${minutes}`
    var seconds = Math.floor(((m.duration / 60) - minutes) * 60);
    seconds = (seconds > 9) ? seconds : `0${seconds}`
    document.querySelector(".time-duration").innerHTML = `00:00/${minutes}:${seconds}`;
}

// Event saat tombol play-pause diklik
document.querySelector(".play-pause").onclick = function () {
    let video = document.querySelector(".my-video");
    if (play_state == 0 || play_state == 2) {
        play_state = 1;
        video.play();
        this.innerHTML = `<i class="fas fa-pause"></i>`;
        document.querySelector(".video-cover").style.opacity = "0";
    } else {
        play_state = 0;
        video.pause();
        this.innerHTML = `<i class="fas fa-play"></i>`;
        document.querySelector(".video-cover").style.opacity = "1";
    }
}

// Event saat video diklik
document.querySelector(".my-video").onclick = function () {
    let video = document.querySelector(".my-video");
    let playPauseButton = document.querySelector(".play-pause");
    if (play_state == 0) {
        play_state = 1;
        video.play();
        playPauseButton.innerHTML = `<i class="fas fa-pause"></i>`;
        document.querySelector(".video-cover").style.opacity = "0";
    } else {
        play_state = 0;
        video.pause();
        playPauseButton.innerHTML = `<i class="fas fa-play"></i>`;
        document.querySelector(".video-cover").style.opacity = "1";
    }
}

// Event saat video cover diklik
document.querySelector(".video-cover").onclick = function () {
    let video = document.querySelector(".my-video");
    let playPauseButton = document.querySelector(".play-pause");
    if (play_state == 0 || play_state == 2) {
        play_state = 1;
        video.play();
        playPauseButton.innerHTML = `<i class="fas fa-pause"></i>`;
        document.querySelector(".video-cover").style.opacity = "0";
    } else {
        play_state = 0;
        video.pause();
        playPauseButton.innerHTML = `<i class="fas fa-play"></i>`;
        document.querySelector(".video-cover").style.opacity = "1";
    }
}

// Event saat tombol fullscreen diklik
document.querySelector('.full-screen').onclick = function () {
    let videoElement = document.querySelector('.video-element');
    let controlBox = document.querySelector('.control-box');
    if (!document.fullscreenElement) {
        if (videoElement.requestFullscreen) {
            videoElement.requestFullscreen();
            controlBox.style.height = '7%';
        } else if (videoElement.webkitRequestFullscreen) { /* Safari */
            videoElement.webkitRequestFullscreen();
            controlBox.style.height = '7%';
        } else if (videoElement.msRequestFullscreen) { /* IE11 */
            videoElement.msRequestFullscreen();
            controlBox.style.height = '7%';
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
            controlBox.style.height = '10%';
        } else if (document.webkitExitFullscreen) { /* Safari */
            document.webkitExitFullscreen();
            controlBox.style.height = '10%';
        } else if (document.msExitFullscreen) { /* IE11 */
            document.msExitFullscreen();
            controlBox.style.height = '10%';
        }
    }
}

// Event saat tombol mute-button diklik
document.querySelector(".mute-button").onclick = function () {
    let video = document.querySelector(".my-video");
    if (vol_state == 1) {
        video.volume = 0;
        vol_state = 0;
        this.innerHTML = `<i class="fas fa-volume-off"></i>`;
        document.querySelector(".volume-button").value = 0;
        document.querySelector(".present-volume").style.transform = `scaleX(0)`;
    } else {
        video.volume = last_vol;
        document.querySelector(".volume-button").value = last_vol;
        vol_state = 1;
        this.innerHTML = `<i class="fas fa-volume-up"></i>`;
        document.querySelector(".present-volume").style.transform = `scaleX(${last_vol})`;
    }
}

// Event saat input volume berubah
document.querySelector(".volume-button").oninput = function () {
    let video = document.querySelector(".my-video");
    video.volume = this.value;
    last_vol = this.value;
    let width = document.querySelector(".volume-button").getBoundingClientRect().width;
    document.querySelector(".present-volume").style.transform = `scaleX(${last_vol})`;
    if (this.value == 0) {
        vol_state = 0;
        document.querySelector(".mute-button").innerHTML = `<i class="fas fa-volume-off"></i>`;
    } else {
        vol_state = 1;
        document.querySelector(".mute-button").innerHTML = `<i class="fas fa-volume-up"></i>`;
    }
}

// Event saat video diputar
document.querySelector(".my-video").ontimeupdate = function () {
    let video = document.querySelector(".my-video");
    let progressSlider = document.querySelector(".progress-slider");
    let completedTrack = document.querySelector(".completed-track");

    let percentage = (video.currentTime / video.duration) * 100;
    progressSlider.value = percentage;
    let width = progressSlider.getBoundingClientRect().width;
    completedTrack.style.width = `${(width * percentage) / 100}px`;

    let minutes = Math.floor(video.duration / 60);
    minutes = (minutes > 9) ? minutes : `0${minutes}`;
    let seconds = Math.floor(((video.duration / 60) - minutes) * 60);
    seconds = (seconds > 9) ? seconds : `0${seconds}`;

    let c_minutes = Math.floor(video.currentTime / 60);
    c_minutes = (c_minutes > 9) ? c_minutes : `0${c_minutes}`;
    let c_seconds = Math.floor(((video.currentTime / 60) - c_minutes) * 60);
    c_seconds = (c_seconds > 9) ? c_seconds : `0${c_seconds}`;

    document.querySelector(".time-duration").innerHTML = `${c_minutes}:${c_seconds}/${minutes}:${seconds}`;

    if (video.duration == video.currentTime) {
        progressSlider.value = 0;
        completedTrack.style.width = `0px`;
        document.querySelector(".play-pause").innerHTML = `<i class="fas fa-redo-alt"></i>`;
        document.querySelector('.video-cover').innerHTML = '<i class="fas fa-redo-alt"></i>';
        play_state = 2;
        document.querySelector(".video-cover").style.opacity = "1";
    }
}

// Event saat progress slider diubah
document.querySelector(".progress-slider").oninput = function () {
    let video = document.querySelector(".my-video");
    let percentage = this.value;
    let ctime = (video.duration * percentage) / 100;
    video.currentTime = ctime;
    let width = document.querySelector(".progress-slider").getBoundingClientRect().width;
    document.querySelector(".completed-track").style.width = `${width * percentage / 100}px`;
}

// Event saat mouse bergerak di area video
document.querySelector(".video-element").onmousemove = function () {
    clearTimeout(a);
    document.querySelector(".video-element .control-box").style.transform = "none";
    document.querySelector(".video-element .control-box").style.opacity = "1";
    document.querySelector(".video-cover").style.height = "95%";
    a = setTimeout(function () {
        document.querySelector(".video-element .control-box").style.transform = "translateY(100%)";
        document.querySelector(".video-element .control-box").style.opacity = "0";
        document.querySelector(".video-cover").style.height = "100%";
    }, 3000)
}

// Event saat video sedang menunggu buffering
document.querySelector('.my-video').onwaiting = function () {
    document.querySelector('.video-cover').innerHTML = '<i class="fas fa-spinner rotating"></i>'
    document.querySelector('.video-cover').style.opacity = '1'
}

// Event saat video siap diputar
document.querySelector('.my-video').oncanplay = function () {
    if (play_state != 0 && play_state != 2) {
        document.querySelector('.video-cover').style.opacity = '0';
    }
    document.querySelector('.video-cover').innerHTML = '<i class="fas fa-play"></i>';
}
