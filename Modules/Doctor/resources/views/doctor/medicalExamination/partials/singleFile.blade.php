<div class="row my-3">
    <div class="col-auto d-flex">
        <form action="{{route('doctor.uploadFile.delete',['id'=>$file->id])}}" method="POST">
            @csrf
            @method('POST')
            <button type="button" class="btn mx-1 btn-delete btn-icon btn-danger">
                <i class="ti icon-base tabler-trash"></i>
            </button>
        </form>
        <a target="_blank"
           class="btn mx-1 btn-icon btn-info" href="{{ route('secure-file.download', ['mediaId' => $file->id]) }}">
            <i class="ti icon-base tabler-eye"></i>
        </a>
        <p class="mx-3 my-2">
            {{Str::limit($file->getCustomProperty('original_name'), 20)}}
        </p>
    </div>

</div>