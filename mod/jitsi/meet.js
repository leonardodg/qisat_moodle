let Meet = {
    domain: "",
    avatarUrl: "",
    displayName: "",
    readyToClose: null,
    password: null,
    idNumber: "",
    options: {
        roomName: null,
        parentNode: null,
        configOverwrite: {
            channelLastN: 4,
            startWithAudioMuted: true,
            startWithVideoMuted: true,
            enableUserRolesBasedOnToken: false
        },
        interfaceConfigOverwrite:{
            APP_NAME: 'QiSat Meet',
            TOOLBAR_BUTTONS:[
                                "microphone",
                                "camera",
                                "closedcaptions",
                                "fullscreen",
                                "fodeviceselection",
                                "hangup",
                                "profile",
                                "chat",
                                "settings",
                                "etherpad",
                                "raisehand",
                                "videoquality",
                                "filmstrip",
                                "feedback",
                                "stats",
                                "shortcuts",
                                "tileview",
                                "mute-everyone"
                        ],
            SHOW_JITSI_WATERMARK: false,
            SHOW_WATERMARK_FOR_GUESTS: false,
            JITSI_WATERMARK_LINK: '',
        },
        width: '100%',
        height: '100%',
    },
    setWatermark: watermark => {
        Meet.options.interfaceConfigOverwrite.JITSI_WATERMARK_LINK = watermark;
    },
    addToolbarButton: toolbarButton => {
        Meet.options.interfaceConfigOverwrite.TOOLBAR_BUTTONS.push(toolbarButton);
    },
    setReadyToClose: url => {
        Meet.readyToClose = url;
    },
    start: () => {
        Meet.options.parentNode = document.querySelector('#meet-jitsi');
        let api = new JitsiMeetExternalAPI(Meet.domain, Meet.options);

        api.executeCommand('displayName', Meet.displayName);
        api.executeCommand('avatarUrl', Meet.avatarUrl);

        api.on('displayNameChange', () => {
            api.executeCommand('displayName', Meet.displayName);
        });

        if(Meet.readyToClose !== null){
            api.on('readyToClose', () => {
                api.dispose();
                location.href = Meet.readyToClose;
            });
            api.on('videoConferenceLeft', () => {
                api.dispose();
                location.href = Meet.readyToClose;
            });
        }

        if(Meet.password !== null) {
            api.addEventListener('participantRoleChanged', event => {
                if (event.role === "moderator") {
                    api.executeCommand('password', Meet.password);    
                }
            });
            
            api.on('passwordRequired', () => {
                api.executeCommand('password', Meet.password);
            });
        }
    }
};


function protectionMask(){
    let elementoMask = document.querySelector('#meet-jitsi-mask');
    if(elementoMask == null) {
        elementoMask = document.createElement('div');
        elementoMask.innerHTML = Meet.idNumber;
        elementoMask.setAttribute("id", "meet-jitsi-mask");
        document.querySelector('body').appendChild(elementoMask);
        elementoMask.style.position = 'absolute';
        elementoMask.style.bottom = '50px';
        elementoMask.style.color = 'rgb(138, 137, 137)';
        elementoMask.style.height = '15px';
        elementoMask.style.animation = "color-change 10s infinite";
        elementoMask.style.fontWeight = "bold";
    }
    
    let marginWidth = Math.floor(Math.random() * window.innerWidth - elementoMask.offsetWidth);
    let marginHeight = Math.floor(Math.random() * window.innerHeight - elementoMask.offsetHeight);

    elementoMask.style.left = marginWidth;
    elementoMask.style.top = marginHeight;

    setTimeout(() => {
        protectionMask();
    }, 3000);
}