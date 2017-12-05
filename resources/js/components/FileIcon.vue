<template>
    <svg-icon class="file-icon" :type="type" :name="name"></svg-icon>
</template>


<script>
export default {

    props: {
        extension: String,
        type: {
            type: String,
            default() {
                return 'svg';
            }
        }
    },


    computed: {

        name() {
            switch (this.extension) {
                case 'folder':
                    return 'folder';

                case '7z':
                case 'pkg':
                case 'rar':
                case 'tar':
                case 'tar.zg':
                case 'z':
                case 'zip':
                    return 'file-archive';

                case 'aac':
                case 'aif':
                case 'cda':
                case 'flac':
                case 'm4a':
                case 'mp3':
                case 'mp4a':
                case 'mpa':
                case 'ogg':
                case 'mid':
                case 'midi':
                case 'wav':
                case 'wma':
                    return 'file-audio';

                case 'doc':
                case 'docx':
                    return 'file-doc';

                case 'xls':
                case 'xlsx':
                    return 'file-excel';

                case 'json':
                    return 'file-json';

                case 'ai':
                case 'eps':
                case 'indb':
                case 'psd':
                case 'sketch':
                    return 'file-layered';

                case 'pdf':
                    return 'file-pdf';

                case 'key':
                case 'odp':
                case 'pps':
                case 'ppt':
                case 'pptx':
                    return 'file-presentation';

                case '3g2':
                case '3gp':
                case 'avi':
                case 'flv':
                case 'h264':
                case 'm4v':
                case 'mvk':
                case 'mp4':
                case 'mpg':
                case 'mpeg':
                case 'mov':
                case 'rm':
                case 'swf':
                case 'vob':
                case 'wmv':
                    return 'file-video';

                case 'xml':
                    return 'file-xml';

                case 'bmp':
                case 'gif':
                case 'ico':
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'tiff':
                    return 'picture';

                default:
                    return 'file-generic';
            }
        }

    }


}
</script>
