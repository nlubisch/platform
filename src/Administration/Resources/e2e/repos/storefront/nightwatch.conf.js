const path = require('path');

module.exports = {
    src_folders: [ path.resolve(__dirname, 'specs')],
    globals_path: path.resolve(__dirname, 'globals.js'),

    test_settings: {
        default: {
            launch_url: `${process.env.APP_URL}/`
        },
        docker: {
            launch_url: 'http://docker.vm:8000/'
        }
    }
};
