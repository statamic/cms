<script>
import Vue from "vue";

export default {

    props: {
        extension: String,
    },

    render(createElement) {
        const compiledTemplate = Vue.compile(this.getIcon());

        return compiledTemplate.render.call(this, createElement);
    },

    computed: {
        name() {
            switch (this.extension.toLowerCase()) {
                case 'folder':
                    return 'folder';
                case '7z':
                case 'pkg':
                case 'rar':
                case 'tar':
                case 'tar.zg':
                case 'z':
                case 'zip':
                    return 'archive';
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
                    return 'audio';
                case 'doc':
                case 'docx':
                case 'epub':
                case 'mobi':
                    return 'doc';
                case 'xls':
                case 'xlsx':
                    return 'excel';
                case 'json':
                    return 'json';
                case 'ai':
                case 'eps':
                case 'fig':
                case 'indb':
                case 'psd':
                case 'sketch':
                    return 'layered';
                case 'pdf':
                    return 'pdf';
                case 'key':
                case 'odp':
                case 'pps':
                case 'ppt':
                case 'pptx':
                    return 'presentation';
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
                    return 'video';
                case 'xml':
                    return 'xml';
                case 'bmp':
                case 'gif':
                case 'ico':
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'raw':
                case 'nef':
                case 'tiff':
                    return 'image';
                default:
                    return 'generic';
            }
        }
    },

    methods: {

        getIcon() {
            return require(`!!html-loader!./../../svg/filetypes/${this.name}.svg`);
        },
    }
}
</script>
