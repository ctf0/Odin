{{-- styles --}}
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bulma/0.7.1/css/bulma.min.css">
<link rel="stylesheet" href="{{ asset('assets/vendor/Odin/style.css') }}"/>

<section id="app" v-cloak>
    {{-- notifications --}}
    <div class="notif-container">
        @if (session('status'))
            <my-notification
                title="{{ session('title') ?? 'Success' }}"
                body="{{ session('status') }}"
                type="{{ session('type') ?? 'success' }}"
                duration="3">
            </my-notification>
        @endif

        <my-notification></my-notification>
    </div>

    {{-- Odin --}}
    <odin inline-template v-cloak
        :translations="{{ json_encode(['ajax_error' => trans('Odin::messages.ajax_error')]) }}"
        :rev-list="{{ json_encode($revisions->pluck('id')) }}">

        <div>
            {{-- overlay --}}
            <div class="odin-animated fadeIn" :class="{'shade' : selected}" @click="toggleRev()"></div>

            <div class="columns">
                <div class="column revisions" ref="revisions">
                    {{-- list --}}
                    <table class="table is-hoverable is-narrow">
                        <tbody>
                            @foreach($revisions as $rev)
                                <tr class="revisions-link"
                                    data-index="{{ $rev->id }}"
                                    @click="toggleRev({{ $rev->id }})">

                                    {{-- user avatar --}}
                                    <td class="has-text-center">
                                        <figure class="image is-24x24">
                                            <img src="{{ $rev->user->avatar ?? '' }}">
                                        </figure>
                                    </td>

                                    {{-- user name --}}
                                    <td>{{ $rev->user->name ?? '' }}</td>

                                    {{-- date --}}
                                    <td>
                                        {{ $rev->created_at->diffForHumans() }}
                                        <strong>"{{ $rev->created_at->format('F j, Y @ h:i:s A') }}"</strong>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- diff --}}
                    <div class="compare-page odin-animated fadeInUp" v-show="selected">

                        {{-- close --}}
                        <div class="is-pulled-right">
                            <button class="delete" @click="toggleRev()"></button>
                        </div>

                        {{-- content --}}
                        <ul id="scrollArea" class="timeline" ref="container">
                            @foreach($revisions as $rev)
                                @php
                                    $html = app('odin')->toHtml($rev);
                                    $class = $rev->event == 'created' ? 'is-link is-outlined' : 'is-success';
                                    $previewCheck = isset($template) && !in_array($rev->event, ['created', 'restored']);
                                @endphp

                                {{-- date --}}
                                <li id="{{ $rev->id }}" class="timeline-header" data-index="{{ $rev->id }}">
                                    <button class="tag is-rounded is-medium is-black revisions-link"
                                        @click.stop="goTo({{ $rev->id }})">
                                        {{ $rev->created_at->diffForHumans() }}
                                    </button>
                                </li>

                                {{-- data --}}
                                <li class="timeline-item" data-index="{{ $rev->id }}">
                                    <div class="timeline-marker is-icon"
                                        :class="{'is-link' : selected == '{{ $rev->id }}'}">
                                        <template v-if="selected == '{{ $rev->id }}'">
                                            <icon name="flag" scale="0.75"></icon>
                                        </template>
                                    </div>

                                    <div class="timeline-content">
                                        <div class="heading">
                                            {{-- event name --}}
                                            <p><span class="title">{{ $rev->event }}</span></p>

                                            {{-- event date --}}
                                            <p>
                                                <span class="subtitle is-6">
                                                    {{ $rev->created_at->format('F j, Y @ h:i:s A') }}
                                                </span>
                                            </p>

                                            {{-- event user --}}
                                            <p>
                                                <small class="subtitle is-6">{{ trans('Odin::messages.by') }}</small>
                                                <span class="subtitle is-5">{{ $rev->user->name ?? '' }}</span>
                                            </p>
                                        </div>

                                        <div>
                                            <section class="compare-page__body">
                                                {{-- body --}}
                                                @if($html)
                                                    {!! $html !!}
                                                @endif

                                                {{-- relations --}}
                                                @if($rev->odin_relations->count())
                                                    @foreach($rev->odin_relations as $key => $val)
                                                        <p class="title">{{ class_basename($key) }}</p>
                                                        <table class="table is-fullwidth Differences DifferencesSideBySide">
                                                            <tbody class="ChangeReplace">
                                                                @foreach($val as $item)
                                                                    <tr>
                                                                        <td class="Left">{{ $item->event == 'Detached' ? app($key)->find($item->relation_id)->misc_title : '' }}</td>
                                                                        <td class="Right">{{ $item->event == 'Attached' ? app($key)->find($item->relation_id)->misc_title : '' }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endforeach
                                                @endif

                                                {{-- no changes --}}
                                                @if(!$rev->odin_relations->count() && !$html)
                                                    <p class="title is-5 is-info notification">
                                                        {{ trans('Odin::messages.no_diff') }}
                                                    </p>
                                                @endif
                                            </section>

                                            {{-- ops --}}
                                            @if(count($revisions) > 1)
                                                <div class="compare-page__footer">
                                                    <div class="level">
                                                        @if($html)
                                                            {{-- preview --}}
                                                            <div class="level-left">
                                                                @if($previewCheck)
                                                                    <div class="level-item">
                                                                        <form action="{{ route('odin.preview', $rev->id) }}"
                                                                            method="POST"
                                                                            target="_blank">
                                                                            {{ csrf_field() }}
                                                                            <input type="hidden" name="template" value="{{ $template }}">
                                                                            <button class="button is-link is-outlined">
                                                                                {{ trans('Odin::messages.preview') }}
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <div class="level-right">
                                                                <div class="level-item">
                                                                    @if($rev->event == 'deleted')
                                                                        {{-- restore softDelete --}}
                                                                        <form action="{{ route('odin.restore.soft', $rev->id) }}"
                                                                            method="POST">
                                                                            {{ method_field('PUT') }}
                                                                            {{ csrf_field() }}
                                                                            <button class="button is-success">
                                                                                {{ trans('Odin::messages.res_model') }}
                                                                            </button>
                                                                        </form>
                                                                    @elseif($rev->event != 'restored')
                                                                        {{-- restore normal --}}
                                                                        <form action="{{ route('odin.restore', $rev->id) }}"
                                                                            method="POST">
                                                                            {{ csrf_field() }}
                                                                            <button class="button {{ $class }}">
                                                                                {{ trans('Odin::messages.res') }}
                                                                            </button>
                                                                        </form>
                                                                    @endif
                                                                </div>

                                                                {{-- remove revision --}}
                                                                <div class="level-item">
                                                                    <form action="{{ route('odin.remove', $rev->id) }}"
                                                                        data-id="{{ $rev->id }}"
                                                                        @submit.prevent="removeRev($event)">
                                                                        {{ csrf_field() }}
                                                                        <button class="button is-danger">
                                                                            {{ trans('Odin::messages.del') }}
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>

                                                        @else
                                                            <div class="level-left"></div>
                                                            <div class="level-right">
                                                                {{-- remove revision --}}
                                                                <div class="level-item">
                                                                    <form action="{{ route('odin.remove', $rev->id) }}"
                                                                        data-id="{{ $rev->id }}"
                                                                        @submit.prevent="removeRev($event)">
                                                                        {{ csrf_field() }}
                                                                        <button class="button is-danger">
                                                                            {{ trans('Odin::messages.del') }}
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>

                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </odin>

</section>

{{-- Footer --}}
<script src="{{ asset('js/app.js') }}"></script>
